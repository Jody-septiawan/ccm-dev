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
     * Display a listing of the Ticket and Ticket Attachment data
     *
     * @param Request $request
     * 
     * @return void
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required',
            ]);
    
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            $result = $this->ticketRepository->getTicketDatatable($request);

            return $result;
        } catch (Throwable $th) {
            return JsonResponse::notFound($th->getMessage()); 
        }
    }

    /**
     * Get Ticket and Ticket Attachment data by id
     *
     * @param int $id
     * 
     * @return void
     */
    public function show($id)
    {
        try {
            $result = $this->ticketRepository->getTicketById($id);

            return JsonResponse::success($result);
        } catch (Throwable $th) {
            return JsonResponse::notFound($th->getMessage()); 
        }
    }

    /**
     * Store Ticket and Ticket Attachment data
     *
     * @param Request $request
     * 
     * @return void
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_pipeline_id' => 'required',
                'user_id' => 'required',
                'company_id' => 'required',
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
                'company_id' => $request->input('company_id'),
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

    /**
     * Change Ticket status
     *
     * @param Request $request
     * @param int $id
     * 
     * @return void
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:open,assigned,in progress,pending,rejected,resolved',
            ]);
    
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            $this->ticketRepository->updateStatus($id, $request->input('status'));

            $result = $this->ticketRepository->getTicketById($id);

            return JsonResponse::success($result, "Data berhasil diubah");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
