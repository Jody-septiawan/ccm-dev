<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketNotification extends Model
{
    protected $guarded = [];

    public function templates()
    {
        return $this->belongsTo(TicketNotificationTemplate::class, "ticket_notification_template_id");
    }
}
