<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $guarded = [];
    
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, "ticket_id");
    }

    public function attachments()
    {
        return $this->hasMany(TicketCommentAttachment::class, "ticket_comment_id");
    }
}
