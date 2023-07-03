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
     * Create Ticket Attachment
     *
     * @param integer $ticket_id
     * @param string $urlPath
     * @param string $filePath
     * @param string $size
     * @param string $type
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