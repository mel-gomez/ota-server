<?php

namespace App\Notifications;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FirstJobPostingNotification extends Notification
{
    use Queueable;

    protected $jobPost;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, JobPost $jobPost)
    {
        $this->jobPost = $jobPost;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'New Job Post',
            'message' => "<ul>
                <li>{$this->user->name} created their first job post.</li>
                <li>Title: {$this->jobPost->title}</li>
                <li>Description: {$this->jobPost->description}</li>
                <li>Created At: {$this->jobPost->created_at}</li>
            </ul>",
            'job_post_id' => $this->jobPost->id,
        ]);
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Job Post',
            'message' => "{$this->user->name} created their first job post",
            'job_post_id' => $this->jobPost->id,
        ]);
    }
}
