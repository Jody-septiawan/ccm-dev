<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketScore extends Model
{
    protected $guarded = [];

    public function rating()
    {
        return $this->belongsTo(Rating::class, "rating_id");
    }
}
