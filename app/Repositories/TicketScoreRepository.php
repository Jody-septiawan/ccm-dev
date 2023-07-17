<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Facades\DB;
use App\Models\TicketScore;

class TicketScoreRepository
{
    protected $model;

    public function __construct(TicketScore $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new ticket score.
     *
     * @param array $data
     * 
     * @return void
     */
    public function store(array $data)
    {
        $model = new $this->model;
        $model->ticket_id = $data['ticket_id'];
        $model->rating_id = $data['rating_id'];
        $model->comment = $data['comment'] ?? null;
        $model->save();

        return $model;
    }

    /**
     * Get ticket score by ticket id
     *
     * @param integer $ticket_id
     * 
     * @return void
     */
    public function getByTicketId(int $ticket_id)
    {
        $model = $this->model->where('ticket_id', $ticket_id)->first();

        return $model;
    }
}