<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCommentAttachment extends Model
{
    protected $guarded = [];

    public function ticketComments()
    {
        return $this->belongsTo(TicketComment::class);
    }
}
