<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
class LowStockNotification extends Notification {
 use Queueable;
 public function __construct(public string $name,public int $quantity,public int $reorderLevel){}
 public function via(object $notifiable):array{return ['database','mail'];}
 public function toMail(object $notifiable):MailMessage{return (new MailMessage)->subject('Low stock alert: '.$this->name)->line("{$this->name} has {$this->quantity} item(s) remaining.")->line("Reorder level: {$this->reorderLevel}.")->action('View inventory', url('/inventory'));}
 public function toArray(object $notifiable):array{return ['message'=>"Low stock: {$this->name} ({$this->quantity} remaining; reorder at {$this->reorderLevel})."];} 
}
