<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TicketRepository;
use App\Repositories\TicketCommentRepository;
use App\Repositories\TicketCommentAttachmentRepository;
use Illuminate\Support\Facades\Validator;
use App\Libs\Json\JsonResponse;
use App\Services\UploadFileService;

class TicketCommentController extends Controller
{
    private $ticketCommentRepository;

    public function __construct(TicketCommentRepository $ticketCommentRepository)
    {
        $this->ticketCommentRepository = $ticketCommentRepository;
    }

    /**
     * Create ticket comment and attachment
     * ------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Store ticket comment data
     * 3. Store ticket comment attachment data
     * ------------------------------------
     *
     * @param Request $request
     * @param UploadFileService $uploadFile
     * @param TicketCommentAttachmentRepository $ticketCommentAttachmentRepository
     * @param string $id => ticket id
     * 
     * @return void
     */
    public function store(Request $request, UploadFileService $uploadFile, TicketCommentAttachmentRepository $ticketCommentAttachmentRepository, TicketRepository $ticketRepository,string $id)
    {
        try {
            // Merge $id parameter to request data
            $request->merge(['ticket_id' => $id]);
            
            // Validate request data
            $validator = Validator::make($request->all(), [
                "ticket_id" => "required",
                "user_id" => "required",
                "message" => "required",
                "attachments" => "array",
                'attachments.*' => 'mimes:jpeg,jpg,png,gif,mp4',
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            $ticketExist = $ticketRepository->getTicketById($id);

            if (!$ticketExist || $ticketExist->company_id != $request->company_id) {
                return JsonResponse::notFound();
            }

            // Store ticket comment data
            $result = $this->ticketCommentRepository->store($request->all());

            // files variable store request files
            $files = $request->file('attachments');
            // attachments variable store attachments data
            $attachments = null;

            // Check if files is not null
            if ($files) {
                // Looping files
                foreach ($files as $file) {
                    // Init file url path
                    $urlPath = null;
                    // Get file size and type
                    $fileSize = $file->getSize();
                    $fileType = $file->getMimeType();

                    // Store file to storage
                    $filePath = $uploadFile->save('ticket_comment_attachment', $file);
            
                    // Check if app environment is production
                    if (app()->environment('production')) {
                        // Get file url from digital ocean space
                        $urlPath = config('app.do_space') . $filePath;
                    } else {
                        // Get file url from storage
                        $urlPath = url($filePath);
                    }

                    // Store ticket attachment data
                    $attachments[] = $ticketCommentAttachmentRepository->store($result->id, $urlPath, $filePath, $fileSize, $fileType);
                }
            }
            
            // Add attachments data to result
            $result['attachments'] = $attachments;

            return JsonResponse::success($result, "Data berhasil ditambahkan");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Update ticket comment and attachment
     * ------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Get ticket comment data by id
     * 3. Check if ticket comment data is not found return error
     * 4. Check if deleted attachment ids is not empty on database
     * 5. Delete attachment data
     * 6. Check if new attachment is not empty on request
     * 7. Store new attachment data
     * 8. Update ticket comment data
     * ------------------------------------
     *
     * @param Request $request
     * @param UploadFileService $uploadFile
     * @param TicketCommentAttachmentRepository $ticketCommentAttachmentRepository
     * @param string $id => ticket comment id
     * 
     * @return void
     */
    public function update(Request $request, UploadFileService $uploadFile, TicketCommentAttachmentRepository $ticketCommentAttachmentRepository, string $id)
    {
        try {
            // Merge $id parameter to request data
            $request->merge(['ticket_comment_id' => $id]);
            
            // Validate request data
            $validator = Validator::make($request->all(), [
                "ticket_comment_id" => "required",
                "message" => "required",
                "newAttachments" => "array",
                "newAttachments.*" => "mimes:jpeg,jpg,png,gif,mp4",
                "deleteAttachmentIds" => "array",
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get ticket comment data by id
            $ticketComment = $this->ticketCommentRepository->getById($id);

            // Return error if ticket comment not found
            if (!$ticketComment || $ticketComment->ticket->company_id != $request->company_id) {
                return JsonResponse::notFound("Data tidak ditemukan");
            }

            // Get request data
            $message = $request->input('message');
            $deleteAttachmentIds = $request->input('deleteAttachmentIds');
            $files = $request->file('newAttachments');

            // Check if deleteAttachmentIds is not null
            if ($deleteAttachmentIds) {
                // Looping ids
                foreach ($deleteAttachmentIds as $deleteAttachmentId) {
                    // Get attachment data by id
                    $attachment = $ticketCommentAttachmentRepository->getById($deleteAttachmentId);

                    // Return error if data not found
                    if (!$attachment)
                    {
                        return JsonResponse::notFound("Data attachment tidak ditemukan");
                    }
                }

                // Delete attachment data
                $ticketCommentAttachmentRepository->destroyBatch($deleteAttachmentIds);
            }

            // Check if files is not null
            if ($files) {
                // Looping files
                foreach ($files as $file) {
                    // Init file url path
                    $urlPath = null;
                    // Get file size and type
                    $fileSize = $file->getSize();
                    $fileType = $file->getMimeType();

                    // Store file to storage
                    $filePath = $uploadFile->save('ticket_comment_attachment', $file);
            
                    // Check if app environment is production
                    if (app()->environment('production')) {
                        // Get file url from digital ocean space
                        $urlPath = config('app.do_space') . $filePath;
                    } else {
                        // Get file url from storage
                        $urlPath = url($filePath);
                    }

                    // Store ticket attachment data
                    $ticketCommentAttachmentRepository->store($id, $urlPath, $filePath, $fileSize, $fileType);
                }
            }

            // Update ticket comment data
            $result = $this->ticketCommentRepository->update($message, $id);

            return JsonResponse::success($result, "Data berhasil diubah");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
