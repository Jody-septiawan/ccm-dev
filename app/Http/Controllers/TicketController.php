<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libs\Json\JsonResponse;
use App\Repositories\TicketRepository;
use App\Repositories\TicketAttachmentRepository;
use App\Services\StorageService;

class TicketController extends Controller
{
    private $ticketRepository;
    private $ticketAttachmentRepository;

    public function __construct(TicketRepository $ticketRepository, TicketAttachmentRepository $ticketAttachmentRepository)
    {
        $this->ticketRepository = $ticketRepository;
        $this->ticketAttachmentRepository = $ticketAttachmentRepository;
    }

    /**
     * Store Ticket and Ticket Attachment data
     *
     * @param Request $request
     * @param StorageService $storageService
     * 
     * @return void
     */
    public function store(Request $request, StorageService $storageService)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_pipeline_id' => 'required',
                'user_id' => 'required',
                'title' => 'required',
                'priority' => 'required|in:low,medium,high',
                'category' => 'required|in:category,delivery,service',
                'subcategory' => 'required',
                'attachments.*' => 'mimes:jpeg,jpg,png,gif,mp4',
            ]);
    
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }
    
            $customer_pipeline_id = $request->input('customer_pipeline_id');
            $user_id = $request->input('user_id');

            // Generate ticket number
            $countTicket = $this->ticketRepository->countCompanyTicket($customer_pipeline_id);
            $ticketNumber = 'TICKET' . sprintf('%02d', $user_id) . sprintf('%03d', $countTicket + 1);

            $data = [
                'customer_pipeline_id' => $customer_pipeline_id,
                'user_id' => $user_id,
                'ticket_number' => $ticketNumber,
                'title' => $request->input('title'),
                'priority' => $request->input('priority'),
                'status' => 'open',
                'category' => $request->input('category'),
                'subcategory' => $request->input('subcategory'),
                'description' => $request->input('description'),
            ];

            $result = $this->ticketRepository->store($data);

            // Upload attachment files
            $files = $request->file('attachments');
            $attachments = null;

            if ($files) {
                foreach ($files as $file) {
                    $attachments[] = $this->ticketAttachmentRepository->store($result->id, $file);
                }
            }

            $result['attachments'] = $attachments;
            
            return JsonResponse::success($result, "Data berhasil ditambahkan");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
