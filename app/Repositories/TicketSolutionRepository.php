<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Facades\DB;
use App\Models\TicketSolution;

class TicketSolutionRepository
{
    protected $model;

    public function __construct(TicketSolution $model)
    {
        $this->model = $model;
    }

    /**
     * Store Ticket Solution data
     *
     * @param array $data
     * 
     * @return void
     */
    public function store(array $data)
    {
        $model = new $this->model;
        $model->ticket_id = $data['ticket_id'];
        $model->title = $data['solution'];
        $model->nominal = $data['nominal'];
        $model->save();

        return $model;
    }

    /**
     * Delete Ticket Solution data by id
     *
     * @param integer $id
     * 
     * @return void
     */
    public function destroy(int $id)
    {
        $model = $this->model->find($id);
        $model->delete();

        return $model;
    }

    /**
     * Delete Ticket Solution data by ticket id
     *
     * @param integer $ticket_id
     * 
     * @return void
     */
    public function destroyByTicketId(int $ticket_id)
    {
        $model = $this->model->where('ticket_id', $ticket_id)->delete();

        return $model;
    }

    /**
     * Get all Ticket Solution data by ticket id
     *
     * @param integer $ticket_id
     * 
     * @return void
     */
    public function getByTicketId(int $ticket_id)
    {
        $model = $this->model->where('ticket_id', $ticket_id)->get();

        return $model;
    }
}