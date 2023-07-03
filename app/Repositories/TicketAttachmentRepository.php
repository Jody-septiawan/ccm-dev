<?php

namespace App\Repositories;

use App\Models\TicketAttachment;
use App\Services\StorageService;

class TicketAttachmentRepository
{
    protected $model;
    protected $storageService;

    public function __construct(TicketAttachment $model, StorageService $storageService)
    {
        $this->model = $model;
        $this->storageService = $storageService;
    }
    
    /**
     * Store Ticket Attachment data
     *
     * @param int $ticket_id
     * @param object $file
     * 
     * @return void
     */
    public function store(int $ticket_id, $urlPath, $filePath, $size, $type)
    {
        $model = new $this->model;
        $model->ticket_id = $ticket_id;
        $model->url = $urlPath;
        $model->filename = $filePath;
        $model->size = $size;
        $model->type = $type;
        $model->save();

        return $model;
    }
}