<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TicketCommentRepository;
use App\Repositories\TicketCommentAttachmentRepository;
use Illuminate\Support\Facades\Validator;
use App\Libs\Json\JsonResponse;
use App\Services\StorageService;

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
     * @param StorageService $storageService
     * @param TicketCommentAttachmentRepository $ticketCommentAttachmentRepository
     * @param string $id
     * 
     * @return void
     */
    public function store(Request $request, StorageService $storageService, TicketCommentAttachmentRepository $ticketCommentAttachmentRepository, string $id)
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
                    // Store file to storage
                    $filePath = $storageService->storage()->put('ticket_comment_attachment', $file, 'public');
                    $urlPath = null;
            
                    // Check if app environment is production
                    if (app()->environment('production')) {
                        // Get file url from digital ocean space
                        $urlPath = config('app.do_space') . $filePath;
                    } else {
                        // Get file url from storage
                        $urlPath = url('storage/' . $filePath);
                    }

                    // Store ticket attachment data
                    $attachments[] = $ticketCommentAttachmentRepository->store($result->id, $urlPath, $filePath, $file->getSize(), $file->getMimeType());
                }
            }
            
            // Add attachments data to result
            $result['attachments'] = $attachments;

            return JsonResponse::success($result, "Data berhasil ditambahkan");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
