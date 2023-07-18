<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Facades\DB;
use App\Models\TicketNotificationTemplate;

class TicketNotificationTemplateRepository
{
    protected $model;

    public function __construct(TicketNotificationTemplate $model)
    {
        $this->model = $model;
    }

    /**
     * Get all ticket notification template as datatable.
     *
     * @param Request $request
     * 
     * @return void
     */
    public function getDatatable(Request $request)
    {
        $company_id = $request->input('company_id');
        $length = $request->input('length', '10');
        $searchValue = $request->input('search');
        $orderBy = $request->input('column', 'id');
        $orderByDir = $request->input('dir', 'desc');

        $type = $request->input('type');

        // Get all data
        $model = $this->model->query();
        $model->where('company_id', $company_id);

        if (!empty($searchValue)) {
            $model->where('name', 'like', "%$searchValue%");
        };

        if (!empty($type)) {
            $model->where('type', $type);
        };

        // Order by
        $model->orderBy($orderBy, $orderByDir);

        // Paginate
        $data = $model->paginate($length);

        // Return datatable collection resource
        return new DataTableCollectionResource($data);
    }

    /**
     * Get ticket notification template by id.
     *
     * @param integer $id
     * 
     * @return void
     */
    public function getById(int $id)
    {
        $model = $this->model->find($id);

        return $model;
    }

    /**
     * Create a new ticket notification template.
     *
     * @param array $data
     * 
     * @return void
     */
    public function store(array $data)
    {
        $model = new $this->model;
        $model->company_id = $data['company_id'];
        $model->plugin_setting_id = $data['plugin_setting_id'];
        $model->name = $data['name'];
        $model->message = $data['message'];
        $model->type = $data['type'];
        $model->save();

        return $model;
    }

    /**
     * Update ticket notification template by id.
     *
     * @param int $id
     * @param array $data
     * 
     * @return void
     */
    public function update(int $id, array $data)
    {
        $model = $this->model->find($id);
        $model->plugin_setting_id = $data['plugin_setting_id'];
        $model->name = $data['name'];
        $model->message = $data['message'];
        $model->type = $data['type'];
        $model->save();

        return $model;
    }

    /**
     * Destroy ticket notification template by id.
     *
     * @param int $id
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
     * Destroy batch ticket notification template by ids.
     *
     * @param array $ids
     * 
     * @return void
     */
    public function destroyBatch(array $ids)
    {
        $model = $this->model->whereIn('id', $ids);
        $model->delete();

        return $model;
    }
}