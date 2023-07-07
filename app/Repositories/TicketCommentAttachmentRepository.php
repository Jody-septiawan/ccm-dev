<?php

namespace App\Repositories;

use App\Models\TicketCommentAttachment;

class TicketCommentAttachmentRepository
{
    protected $model;

    public function __construct(TicketCommentAttachment $model)
    {
        $this->model = $model;
    }
    
    /**
     * Create Ticket Comment Attachment
     *
     * @param integer $ticket_id
     * @param string $urlPath
     * @param string $filePath
     * @param string $size
     * @param string $type
     * 
     * @return void
     */
    public function store(int $ticket_comment_id, string $urlPath, string $filePath, string $size, string $type)
    {
        $model = new $this->model;
        $model->ticket_comment_id = $ticket_comment_id; 
        $model->url = $urlPath;
        $model->filename = $filePath;
        $model->size = $size;
        $model->type = $type;
        $model->save();

        return $model;
    }

    /**
     * Get Ticket Comment Attachment by id
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
     * Destroy batch Ticket Comment Attachment by ids
     *
     * @param array $ids
     * 
     * @return void
     */
    public function destroyBatch(array $ids)
    {
        // Find ticket comment attachment by id and Delete the attachment
        $model = $this->model->whereIn('id', $ids);
        $model->delete();

        return $model;
    }
}