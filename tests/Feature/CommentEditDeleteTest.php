<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Decision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_edit_own_comment()
    {
        // Crear usuario, decisión y comentario
        $user = User::factory()->create();
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        $comment = Comment::create([
            'decision_id' => $decision->id,
            'user_id' => $user->id,
            'content' => 'Este es un comentario de prueba inicial'
        ]);

        // Actuar como el usuario que creó el comentario
        $response = $this->actingAs($user)
            ->putJson("/api/decisions/{$decision->id}/comments/{$comment->id}", [
                'content' => 'Este es el comentario editado con contenido nuevo'
            ]);

        // Verificar respuesta exitosa
        $response->assertStatus(200);
        
        // Verificar que el comentario se actualizó en la base de datos
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Este es el comentario editado con contenido nuevo'
        ]);
    }

    public function test_user_cannot_edit_others_comment()
    {
        // Crear usuarios, decisión y comentario
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $decision = Decision::create([
            'user_id' => $user1->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        $comment = Comment::create([
            'decision_id' => $decision->id,
            'user_id' => $user1->id,
            'content' => 'Este es un comentario de prueba inicial'
        ]);

        // Actuar como otro usuario e intentar editar el comentario
        $response = $this->actingAs($user2)
            ->putJson("/api/decisions/{$decision->id}/comments/{$comment->id}", [
                'content' => 'Intentando editar el comentario de otro usuario'
            ]);

        // Verificar que se deniega el acceso
        $response->assertStatus(403);
        
        // Verificar que el comentario no se modificó
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Este es un comentario de prueba inicial'
        ]);
    }

    public function test_user_can_delete_own_comment()
    {
        // Crear usuario, decisión y comentario
        $user = User::factory()->create();
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        $comment = Comment::create([
            'decision_id' => $decision->id,
            'user_id' => $user->id,
            'content' => 'Este es un comentario que será eliminado'
        ]);

        $commentId = $comment->id;

        // Actuar como el usuario que creó el comentario
        $response = $this->actingAs($user)
            ->deleteJson("/api/decisions/{$decision->id}/comments/{$comment->id}");

        // Verificar respuesta exitosa
        $response->assertStatus(200);
        
        // Verificar que el comentario ya no existe en la base de datos
        $this->assertDatabaseMissing('comments', [
            'id' => $commentId
        ]);
    }

    public function test_user_cannot_delete_others_comment()
    {
        // Crear usuarios, decisión y comentario
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $decision = Decision::create([
            'user_id' => $user1->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        $comment = Comment::create([
            'decision_id' => $decision->id,
            'user_id' => $user1->id,
            'content' => 'Este es un comentario que no debería ser eliminado por otro usuario'
        ]);

        // Actuar como otro usuario e intentar eliminar el comentario
        $response = $this->actingAs($user2)
            ->deleteJson("/api/decisions/{$decision->id}/comments/{$comment->id}");

        // Verificar que se deniega el acceso
        $response->assertStatus(403);
        
        // Verificar que el comentario sigue existiendo
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id
        ]);
    }

    public function test_comment_validation_on_edit()
    {
        // Crear usuario, decisión y comentario
        $user = User::factory()->create();
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        $comment = Comment::create([
            'decision_id' => $decision->id,
            'user_id' => $user->id,
            'content' => 'Este es un comentario de prueba inicial'
        ]);

        // Intentar editar el comentario con contenido demasiado corto
        $response = $this->actingAs($user)
            ->putJson("/api/decisions/{$decision->id}/comments/{$comment->id}", [
                'content' => 'Corto'
            ]);

        // Verificar que la validación falla
        $response->assertStatus(422);
        
        // Verificar que el comentario no se modificó
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Este es un comentario de prueba inicial'
        ]);
    }
}
