<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, "ticket_id");
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class, "ticket_id");
    }

    public function solution()
    {
        return $this->hasOne(TicketSolution::class, "ticket_id");
    }
}
