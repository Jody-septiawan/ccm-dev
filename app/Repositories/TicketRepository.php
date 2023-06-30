<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use App\Models\Ticket;

class TicketRepository
{
    protected $model;

    public function __construct(Ticket $model)
    {
        $this->model = $model;
    }
    
    /**
     * Store Ticket data
     *
     * @param object $data
     * 
     * @return void
     */
    public function store($data)
    {
        $model = new $this->model;
        $model->customer_pipeline_id = $data['customer_pipeline_id'];
        $model->user_id = $data['user_id'];
        $model->company_id = $data['company_id'];
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

    /**
     * Count company ticket by customer pipeline id
     *
     * @param int $customer_pipeline_id
     * 
     * @return void
     */
    public function countCompanyTicket($customer_pipeline_id)
    {
        $model = $this->model->where('customer_pipeline_id', $customer_pipeline_id)->count();

        return $model;
    }

    /**
     * Get all Ticket and Ticket Attachment data as datatable
     *
     * @param Request $request
     * 
     * @return void
     */
    public function getTicketDatatable(Request $request)
    {
        $company_id = $request->input('company_id');
        $length = $request->input('length', '10');
        $searchValue = $request->input('search');
        $orderBy = $request->input('column', 'id');
        $orderByDir = $request->input('dir', 'desc');

        $model = $this->model->query();
        $model->where('company_id', $company_id);
        if (!empty($searchValue)) {
            $model->where('title', 'like', "%$searchValue%");
        }

        $model->with(['attachments']);
        $model->orderBy($orderBy, $orderByDir);

        $data = $model->paginate($length);

        return new DataTableCollectionResource($data);
    }

    /**
     * Get ticket and ticket attachment data by id
     *
     * @param int $id
     * 
     * @return void
     */
    public function getTicketById($id)
    {
        $model = $this->model->with(['attachments'])->find($id);

        return $model;
    }
}