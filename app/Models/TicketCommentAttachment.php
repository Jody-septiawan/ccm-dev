<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCommentAttachment extends Model
{
    protected $guarded = [];

    public function ticketCommets()
    {
        return $this->belongsTo(TicketComment::class);
    }
}
