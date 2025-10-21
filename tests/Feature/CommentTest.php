<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Decision;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que los comentarios pueden ser listados para una decisión.
     */
    public function test_comments_can_be_listed_for_a_decision(): void
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Crear una decisión
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        // Crear varios comentarios para la decisión
        $comments = [];
        for ($i = 0; $i < 3; $i++) {
            $comments[] = Comment::create([
                'user_id' => $user->id,
                'decision_id' => $decision->id,
                'content' => "This is test comment number " . ($i + 1) . " for the decision. It needs to be at least 10 chars long.",
            ]);
        }

        // Hacer una petición GET a la API de comentarios
        $response = $this->getJson("/api/decisions/{$decision->id}/comments");

        // Verificar que la respuesta es correcta
        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'decision_id',
                        'user_id',
                        'content',
                        'created_at',
                        'updated_at',
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'avatar',
                            'decisions_count',
                            'votes_count'
                        ]
                    ]
                ],
                'first_page_url',
                'from',
                'last_page',
            ]);
    }

    /**
     * Test que un usuario autenticado puede crear un comentario.
     */
    public function test_authenticated_user_can_create_comment(): void
    {
        // Crear un usuario
        $user = User::factory()->create([
            'karma' => 0,
        ]);

        // Crear una decisión
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);

        // Datos del comentario a crear
        $commentData = [
            'content' => 'This is a test comment for the decision. It needs to be at least 10 chars long.',
        ];

        // Hacer una petición POST a la API de comentarios (autenticado como el usuario)
        $response = $this->actingAs($user)
            ->postJson("/api/decisions/{$decision->id}/comments", $commentData);

        // Verificar que la respuesta es correcta
        $response->assertStatus(201)
            ->assertJson([
                'content' => $commentData['content'],
                'user_id' => $user->id,
                'decision_id' => $decision->id,
            ])
            ->assertJsonStructure([
                'id',
                'user_id',
                'decision_id',
                'content',
                'created_at',
                'updated_at',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'avatar',
                ]
            ]);

        // Verificar que el comentario existe en la base de datos
        $this->assertDatabaseHas('comments', [
            'content' => $commentData['content'],
            'user_id' => $user->id,
            'decision_id' => $decision->id,
        ]);

        // Verificar que el karma del usuario se incrementó en +5
        $this->assertEquals(5, $user->fresh()->karma);
    }

    /**
     * Test que un usuario no autenticado no puede crear un comentario.
     */
    public function test_unauthenticated_user_cannot_create_comment(): void
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Crear una decisión
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);

        // Datos del comentario a crear
        $commentData = [
            'content' => 'This is a test comment for the decision. It needs to be at least 10 chars long.',
        ];

        // Hacer una petición POST a la API de comentarios (sin autenticar)
        $response = $this->postJson("/api/decisions/{$decision->id}/comments", $commentData);

        // Verificar que la respuesta es un error de autenticación
        $response->assertStatus(401);

        // Verificar que el comentario NO existe en la base de datos
        $this->assertDatabaseMissing('comments', [
            'content' => $commentData['content'],
            'decision_id' => $decision->id,
        ]);
    }

    /**
     * Test que la validación de contenido funciona.
     */
    public function test_comment_content_validation(): void
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Crear una decisión
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);

        // Caso 1: Contenido demasiado corto (menos de 10 caracteres)
        $response = $this->actingAs($user)
            ->postJson("/api/decisions/{$decision->id}/comments", [
                'content' => 'Too short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);

        // Caso 2: Contenido demasiado largo (más de 1000 caracteres)
        $longContent = str_repeat('a', 1001);
        $response = $this->actingAs($user)
            ->postJson("/api/decisions/{$decision->id}/comments", [
                'content' => $longContent,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);

        // Caso 3: Contenido vacío
        $response = $this->actingAs($user)
            ->postJson("/api/decisions/{$decision->id}/comments", [
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /**
     * Test que los comentarios se ordenan correctamente (el más reciente primero).
     */
    public function test_comments_are_ordered_by_most_recent_first(): void
    {
        // Crear un usuario
        $user = User::factory()->create();

        // Crear una decisión
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);

        // Crear comentarios con diferentes fechas
        $olderComment = Comment::create([
            'user_id' => $user->id,
            'decision_id' => $decision->id,
            'content' => "This is the older comment. Created first.",
            'created_at' => now()->subDays(2),
        ]);

        // Asegurarse de que hay una diferencia de tiempo entre los comentarios
        sleep(1);

        $newerComment = Comment::create([
            'user_id' => $user->id,
            'decision_id' => $decision->id,
            'content' => "This is the newer comment. Created later.",
            'created_at' => now(),
        ]);

        // Hacer una petición GET a la API de comentarios
        $response = $this->getJson("/api/decisions/{$decision->id}/comments");

        // Verificar que el comentario más reciente aparece primero
        $response->assertStatus(200);

        // Extraer los datos de la respuesta
        $responseData = json_decode($response->getContent(), true);

        // Verificar que hay 2 comentarios
        $this->assertCount(2, $responseData['data']);

        // Verificar que el primer comentario es el más reciente (el más nuevo debe aparecer primero)
        $this->assertEquals($newerComment->id, $responseData['data'][0]['id']);

        // Verificar que el segundo comentario es el más antiguo
        $this->assertEquals($olderComment->id, $responseData['data'][1]['id']);

        // Verificar explícitamente que los comentarios están ordenados por created_at descendente (más reciente primero)
        $firstCreatedAt = Carbon::parse($responseData['data'][0]['created_at']);
        $secondCreatedAt = Carbon::parse($responseData['data'][1]['created_at']);
        $this->assertTrue($firstCreatedAt->isAfter($secondCreatedAt), 'Los comentarios no están ordenados con el más reciente primero');
    }
}