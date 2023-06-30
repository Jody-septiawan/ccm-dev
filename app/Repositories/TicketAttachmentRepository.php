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
    public function store(int $ticket_id, $file)
    {
        $filePath = $this->storageService->storage()->put('customer_case_management', $file, 'public');
        $urlPath = null;

        if (app()->environment('production')) {
            $urlPath = config('app.do_space') . $filePath;
        } else {
            $urlPath = url('storage/' . $filePath);
        }

        $model = new $this->model;
        $model->ticket_id = $ticket_id;
        $model->url = $urlPath;
        $model->filename = $filePath;
        $model->size = $file->getSize();
        $model->type = $file->getMimeType();
        $model->save();

        return $model;
    }
}