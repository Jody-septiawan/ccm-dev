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
     * @param array $data
     * 
     * @return void
     */
    public function store(array $data)
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
     * Count company ticket by company id
     *
     * @param int $company_id
     * 
     * @return void
     */
    public function countCompanyTicket(int $company_id)
    {
        $model = $this->model->where('company_id', $company_id)->count();

        return $model;
    }

    /**
     * Get all Ticket and Ticket Attachment data as datatable
     * ----------------------------
     * Desc:
     * This function is used for datatable in Ticket.vue,
     * to get all Ticket and Ticket Attachment data
     * and then show it as datatable.
     * ----------------------------
     * Filters:
     * - company_id
     * - length
     * - search
     * - column
     * - dir
     * - search by title
     * ----------------------------
     *
     * @param Request $request
     * 
     * @return void
     */
    public function getTicketDatatable(Request $request)
    {
        // Get all request
        $company_id = $request->input('company_id');
        $length = $request->input('length', '10');
        $searchValue = $request->input('search');
        $orderBy = $request->input('column', 'id');
        $orderByDir = $request->input('dir', 'desc');

        // Get all data
        $model = $this->model->query();
        $model->where('company_id', $company_id);

        // Search by title
        if (!empty($searchValue)) {
            $model->where('title', 'like', "%$searchValue%");
        }

        // Relation to attachment and Order by
        $model->with(['attachments', 'comments.attachments']);
        $model->orderBy($orderBy, $orderByDir);

        // Paginate
        $data = $model->paginate($length);

        // Return datatable collection resource
        return new DataTableCollectionResource($data);
    }

    /**
     * Get ticket and ticket attachment data by id
     *
     * @param int $id
     * 
     * @return void
     */
    public function getTicketById(int $id)
    {
        $model = $this->model->with(['attachments', 'comments.attachments'])->find($id);

        return $model;
    }

    /**
     * Change ticket status by ticket id
     *
     * @param int $id
     * @param string $status
     * 
     * @return void
     */
    public function updateStatus(int $id, string $status)
    {
        // Find ticket by id and Change the status
        $model = $this->model->find($id);
        $model->status = $status;
        $model->save();

        return $model;
    }

    /**
     * Destroy ticket data by id
     *
     * @param int $id
     * 
     * @return void
     */
    public function destroy(int $id)
    {
        // Find ticket by id and Delete the ticket
        $model = $this->model->find($id);
        $model->delete();

        return $model;
    }

    /**
     * Destroy batch Ticket data by list id
     *
     * @param array $ids
     * 
     * @return void
     */
    public function destroyBatch(array $ids)
    {
        // Find ticket by id and Delete the ticket
        $model = $this->model->whereIn('id', $ids);
        $model->delete();

        return $model;
    }

    /**
     * Update ticket data by id
     *
     * @param int $id
     * @param array $data
     * 
     * @return void
     */
    public function update(int $id, array $data)
    {
        $model = $this->model->find($id);
        $model->user_id = $data['user_id'];
        $model->title = $data['title'];
        $model->priority = $data['priority'];
        $model->category = $data['category'];
        $model->subcategory = $data['subcategory'];
        $model->description = $data['description'] ?? $model->description;
        $model->save();

        return $model;
    }
}