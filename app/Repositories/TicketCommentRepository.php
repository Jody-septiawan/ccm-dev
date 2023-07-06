<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use App\Models\TicketComment;

class TicketCommentRepository
{
    protected $model;

    public function __construct(TicketComment $model)
    {
        $this->model = $model;
    }
    
    /**
     * Store Ticket comment data
     *
     * @param array $data
     * 
     * @return void
     */
    public function store(array $data)
    {
        $model = new $this->model;
        $model->ticket_id = $data['ticket_id'];
        $model->user_id = $data['user_id'];
        $model->message = $data['message'];
        $model->save();

        return $model;
    }
}