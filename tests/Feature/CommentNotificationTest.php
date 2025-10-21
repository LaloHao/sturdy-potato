<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\CommentNotification;
use App\Models\Decision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a notification is created when a user comments on another user's decision.
     */
    public function test_owner_is_notified_when_decision_receives_comment(): void
    {
        // Create decision owner
        $owner = User::factory()->create();

        // Create decision
        $decision = Decision::create([
            'user_id' => $owner->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);

        // Create commenter (different user)
        $commenter = User::factory()->create();
        
        // Initial check - no notifications should exist
        $this->assertEquals(0, CommentNotification::count());

        // Act as commenter and add a comment
        $response = $this->actingAs($commenter)
            ->postJson("/api/decisions/{$decision->id}/comments", [
                'content' => 'This is a test comment on the decision'
            ]);

        // Assert successful comment creation
        $response->assertStatus(201);

        // Assert notification was created for the decision owner
        $this->assertEquals(1, CommentNotification::count());
        
        // Get and verify the notification details
        $notification = CommentNotification::first();
        $this->assertEquals($owner->id, $notification->user_id);
        $this->assertEquals($commenter->id, $notification->commenter_id);
        $this->assertEquals($decision->id, $notification->decision_id);
        $this->assertFalse($notification->read);
        $this->assertStringContainsString('This is a test comment', $notification->comment_preview);
    }

    /**
     * Test that no notification is created when a user comments on their own decision.
     */
    public function test_no_notification_when_owner_comments_on_own_decision(): void
    {
        // Create decision owner
        $owner = User::factory()->create();

        // Create decision
        $decision = Decision::create([
            'user_id' => $owner->id,
            'title' => 'Test Decision',
            'context' => 'This is a test decision context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);

        // Initial check - no notifications should exist
        $this->assertEquals(0, CommentNotification::count());
        
        // Act as owner and add a comment to their own decision
        $response = $this->actingAs($owner)
            ->postJson("/api/decisions/{$decision->id}/comments", [
                'content' => 'This is a comment by the decision owner'
            ]);

        // Assert successful comment creation
        $response->assertStatus(201);

        // Assert no notification was created
        $this->assertEquals(0, CommentNotification::count());
    }
    
    /**
     * Test that a notification can be marked as read.
     */
    public function test_mark_notification_as_read(): void
    {
        // Create decision owner
        $owner = User::factory()->create();
        
        // Create a different user who will comment
        $commenter = User::factory()->create();
        
        // Create decision
        $decision = Decision::create([
            'user_id' => $owner->id,
            'title' => 'Test Decision',
            'context' => 'This is a test context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        // Create a comment
        $comment = Comment::create([
            'user_id' => $commenter->id,
            'decision_id' => $decision->id,
            'content' => 'This is a test comment for notification test',
        ]);
        
        // Create a notification manually
        $notification = CommentNotification::create([
            'user_id' => $owner->id,
            'comment_id' => $comment->id,
            'decision_id' => $decision->id,
            'commenter_id' => $commenter->id,
            'comment_preview' => substr($comment->content, 0, 100),
            'read' => false,
        ]);
        
        // Login as decision owner
        $this->actingAs($owner);
        
        // Verify notification is unread
        $this->assertFalse($notification->fresh()->read);
        
        // Mark notification as read
        $response = $this->postJson("/api/notifications/{$notification->id}/mark-as-read");
        $response->assertStatus(200);
        
        // Verify notification is now read
        $this->assertTrue($notification->fresh()->read);
    }

    /**
     * Test that all notifications can be marked as read.
     */
    public function test_mark_all_notifications_as_read(): void
    {
        // Create a user and a commenter
        $user = User::factory()->create();
        $commenter1 = User::factory()->create();
        $commenter2 = User::factory()->create();
        
        // Create a decision for notifications
        $decision = Decision::create([
            'user_id' => $user->id,
            'title' => 'Test Decision',
            'context' => 'This is a test context',
            'type' => 'technical',
            'is_anonymous' => false,
            'status' => 'open',
            'expires_at' => now()->addDays(7),
        ]);
        
        // Create comments
        $comment1 = Comment::create([
            'user_id' => $commenter1->id,
            'decision_id' => $decision->id,
            'content' => 'Test comment 1',
        ]);
        
        $comment2 = Comment::create([
            'user_id' => $commenter1->id,
            'decision_id' => $decision->id,
            'content' => 'Test comment 2',
        ]);
        
        $comment3 = Comment::create([
            'user_id' => $commenter2->id,
            'decision_id' => $decision->id,
            'content' => 'Test comment 3',
        ]);
        
        // Create 3 notifications for the user
        CommentNotification::create([
            'user_id' => $user->id,
            'comment_id' => $comment1->id,
            'decision_id' => $decision->id,
            'commenter_id' => $commenter1->id,
            'comment_preview' => 'Test comment 1',
            'read' => false,
        ]);
        
        CommentNotification::create([
            'user_id' => $user->id,
            'comment_id' => $comment2->id,
            'decision_id' => $decision->id,
            'commenter_id' => $commenter1->id,
            'comment_preview' => 'Test comment 2',
            'read' => false,
        ]);
        
        CommentNotification::create([
            'user_id' => $user->id,
            'comment_id' => $comment3->id,
            'decision_id' => $decision->id,
            'commenter_id' => $commenter2->id,
            'comment_preview' => 'Test comment 3',
            'read' => false,
        ]);
        
        // Login as the user
        $this->actingAs($user);
        
        // Verify all notifications are unread
        $this->assertEquals(3, CommentNotification::where('user_id', $user->id)->where('read', false)->count());
        
        // Mark all as read
        $response = $this->postJson("/api/notifications/mark-all-read");
        $response->assertStatus(200);
        
        // Verify all notifications are now read
        $this->assertEquals(0, CommentNotification::where('user_id', $user->id)->where('read', false)->count());
        $this->assertEquals(3, CommentNotification::where('user_id', $user->id)->where('read', true)->count());
    }
}
