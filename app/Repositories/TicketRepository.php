<?php

namespace App\Repositories;

use App\Models\Ticket;

class TicketRepository
{
    protected $model;

    public function __construct(Ticket $model)
    {
        $this->model = $model;
    }
    
    public function store($data)
    {
        $model = new $this->model;
        $model->customer_pipeline_id = $data['customer_pipeline_id'];
        $model->user_id = $data['user_id'];
        $model->ticket_number = $data['ticket_number'];
        $model->title = $data['title'];
        $model->priority = $data['priority'];
        $model->status = $data['status'];
        $model->category = $data['category'];
        $model->subcategory = $data['subcategory'];
        $model->description = $data['description'];
        $model->save();

        return $model;
    }

    public function countCompanyTicket($customer_pipeline_id)
    {
        $model = $this->model->where('customer_pipeline_id', $customer_pipeline_id)->count();

        return $model;
    }
}