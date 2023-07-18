<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Illuminate\Support\Facades\DB;
use App\Models\TicketNotification;

class TicketNotificationRepository
{
    protected $model;

    public function __construct(TicketNotification $model)
    {
        $this->model = $model;
    }

    /**
     * Create ticket notification.
     *
     * @param integer $ticket_id
     * @param integer $ticket_notification_template_id
     * 
     * @return void
     */
    public function store(int $ticket_id, int $ticket_notification_template_id)
    {
        $model = new $this->model;
        $model->ticket_id = $ticket_id;
        $model->ticket_notification_template_id = $ticket_notification_template_id;
        $model->save();

        return $model;
    }

    /**
     * Update ticket notification.
     *
     * @param integer $id
     * @param integer $ticket_id
     * @param integer $ticket_notification_template_id
     * 
     * @return void
     */
    public function update(int $id, int $ticket_id, int $ticket_notification_template_id)
    {
        $model = $this->model->find($id);
        $model->ticket_id = $ticket_id;
        $model->ticket_notification_template_id = $ticket_notification_template_id;
        $model->save();

        return $model;
    }

    /**
     * Delete ticket notification.
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
}