<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libs\Json\JsonResponse;
use App\Repositories\TicketRepository;
use App\Repositories\TicketAttachmentRepository;
use App\Repositories\TicketSolutionRepository;
use App\Repositories\TicketNotificationTemplateRepository;
use App\Repositories\TicketNotificationRepository;
use App\Services\StorageService;
use App\Services\UploadFileService;
use App\Services\ExternalAPIs\CrmAPI;
use App\Services\TextFormat;
use App\Services\TextMessage;

class TicketController extends Controller
{
    private $ticketRepository;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Display a listing of the Ticket and Ticket Attachment data
     * --------------------------------------------
     * Flow:
     * 1. Get company_id from request and validate is required
     * 2. Get all Ticket data by company_id using TicketRepository->getTicketDatatable()
     * 3. Get all Ticket Attachment data by company_id using TicketAttachmentRepository->getTicketAttachmentDatatable()
     * 4. Get user assigned to ticket data by id
     * 5. Get user comments
     * --------------------------------------------
     *
     * @param Request $request
     * 
     * @return void
     */
    public function index(Request $request)
    {
        try {
            $request->merge(['company_id' => $request->company_id]);

            // Validate request data
            $validator = Validator::make($request->all(), [
                'company_id' => 'required',
            ]);
    
            // Check if company_id is empty return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            $result = $this->ticketRepository->getTicketDatatable($request);

            $crmAPI = new CrmAPI();

            foreach ($result as $item) {

                if (count($item->notifications) > 0) {
                    // Split Notification create and close
                    $notificationCreate = $item->notifications->where('templates.type', 'create')->first();
                    $notificationClose = $item->notifications->where('templates.type', 'close')->first();
                        
                    // Remove notifications from result
                    unset($item->notifications);
                    
                    // Check if notificationCreate and notificationClose is null
                    $notificationCreate = $notificationCreate->templates ?? null;
                    $notificationClose = $notificationClose->templates ?? null;

                    // Get plugin setting data by plugin_setting_id
                    if ($notificationCreate) {
                        $plugin_setting = $crmAPI->get("plugin/setting/$notificationCreate->plugin_setting_id");
                        $notificationCreate->plugin_setting = $plugin_setting->data;
                    }

                    if ($notificationClose) {
                        $plugin_setting = $crmAPI->get("plugin/setting/$notificationClose->plugin_setting_id");
                        $notificationClose->plugin_setting = $plugin_setting->data;
                    }
                    
                    // Add notifications to result with create and close key
                    $item->notifications = [
                        'create' => $notificationCreate,
                        'close' => $notificationClose,
                    ];
                } else {
                    unset($item->notifications);
                    $item->notifications = null;
                }

                // Get customer pipeline data by id
                if ($item->customer_pipeline_id) {
                    $customer_pipeline = $crmAPI->get("crm/customer/pipeline/detail/$item->customer_pipeline_id");
                    $item->customer_pipeline = $customer_pipeline->data;
                }

                // Get user assigned to ticket data by id
                $assigned_to = $crmAPI->get("crm/company/member/$item->user_id");
                $item->assigned_to = $assigned_to->data;
            };
    

            return $result;
        } catch (Throwable $th) {
            return JsonResponse::notFound($th->getMessage()); 
        }
    }

    /**
     * Get Ticket and Ticket Attachment data by id
     * --------------------------------------------
     * Flow:
     * 1. Get id from parameter
     * 2. Get Ticket data by id using TicketRepository->getTicketById()
     * 3. Get user assigned to ticket data by id
     * 4. Get user comments
     * --------------------------------------------
     *
     * @param string $id
     * 
     * @return void
     */
    public function show(string $id)
    {
        try {
            // Validate request data
            $validator = Validator::make(['id' => $id], [
                'id' => 'required',
            ]);
    
            // Check if id is empty or not integer return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get Ticket data by id
            // result variable will be null if data not found
            // result variable will be object if data found
            $result = $this->ticketRepository->getTicketById($id);

            if (!$result) {
                return JsonResponse::notFound("Data tidak ditemukan");
            }

            $crmAPI = new CrmAPI();

            if (count($result->notifications) > 0) {
                // Split Notification create and close
                $notificationCreate = $result->notifications->where('templates.type', 'create')->first();
                $notificationClose = $result->notifications->where('templates.type', 'close')->first();
                    
                // Remove notifications from result
                unset($result->notifications);

                // Check if notificationCreate and notificationClose is null
                $notificationCreate = $notificationCreate->templates ?? null;
                $notificationClose = $notificationClose->templates ?? null;

                // Get plugin setting data by plugin_setting_id
                if ($notificationCreate) {
                    $plugin_setting = $crmAPI->get("plugin/setting/$notificationCreate->plugin_setting_id");
                    $notificationCreate->plugin_setting = $plugin_setting->data;
                }

                if ($notificationClose) {
                    $plugin_setting = $crmAPI->get("plugin/setting/$notificationClose->plugin_setting_id");
                    $notificationClose->plugin_setting = $plugin_setting->data;
                }
                
                // Add notifications to result with create and close key
                $result->notifications = [
                    'create' => $notificationCreate,
                    'close' => $notificationClose,
                ];
            } else {
                unset($result->notifications);
                $result->notifications = null;
            }

            // Get customer pipeline data by id
            if ($result->customer_pipeline_id) {
                $customer_pipeline = $crmAPI->get("crm/customer/pipeline/detail/$result->customer_pipeline_id");
                $result->customer_pipeline = $customer_pipeline->data;
            }

            // Get user assigned to ticket data by id
            $assigned_to = $crmAPI->get("crm/company/member/$result->user_id");
            $result->assigned_to = $assigned_to->data;

            // Get user comments
            foreach ($result->comments as $comment) {
                $comment->sender = $crmAPI->get("crm/company/member/$comment->user_id")->data;
            };

            return JsonResponse::success($result);
        } catch (Throwable $th) {
            return JsonResponse::notFound($th->getMessage()); 
        }
    }

    /**
     * Store Ticket and Ticket Attachment data
     * --------------------------------------------
     * Flow:
     * 1. Get request data and validate
     * 2. Generate ticket number
     * 3. Store Ticket data using TicketRepository->store()
     * 4. Change status customer pipeline to "Komplain"
     * 5. Upload attachment files
     * 6. Store Ticket Attachment data using TicketAttachmentRepository->store()
     * --------------------------------------------
     *
     * @param Request $request
     * @param UploadFile $uploadFile
     * @param TicketAttachmentRepository $ticketAttachmentRepository
     * 
     * @return void
     */
    public function store(Request $request, UploadFileService $uploadFile, TicketAttachmentRepository $ticketAttachmentRepository, TicketNotificationTemplateRepository $icketNotificationTemplateRepository, TicketNotificationRepository $ticketNotificationRepository)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'title' => 'required',
                'priority' => 'required|in:low,medium,high',
                'category' => 'required|in:product,delivery,service',
                'subcategory' => 'required',
                'attachments.*' => 'mimes:jpeg,jpg,png,gif,mp4',
            ]);
    
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get data from request
            $customer_pipeline_id = $request->input('customer_pipeline_id');
            $notification_template_create_id = $request->input('notification_template_create_id');
            $notification_template_close_id = $request->input('notification_template_close_id');
            $user_id = $request->input('user_id');
            $company_id = $request->company_id;
 
            // Check if customer pipeline id is exist
            if ($customer_pipeline_id)
            {
                if ($notification_template_create_id)
                {
                    // Check if notification template create and close is exist
                    $templateCreate = $icketNotificationTemplateRepository->getById($notification_template_create_id);
                    
                    if (!$templateCreate || $templateCreate->type != 'create')
                    {
                        return JsonResponse::notFound('Notification template create not found');
                    }
                }
                
                if ($notification_template_close_id)
                {
                    // Check if notification template create and close is exist
                    $templateClose = $icketNotificationTemplateRepository->getById($notification_template_close_id);

                    if (!$templateClose || $templateClose->type != 'close')
                    {
                        return JsonResponse::notFound('Notification template close not found');
                    }
                }
            }

            // Generate ticket number
            // Count ticket exist by company_id
            // Add 1 to count ticket
            // Generate ticket number with format TICKET{company_id}{count_ticket}
            $countTicket = $this->ticketRepository->countCompanyTicket($company_id);
            $ticketNumber = 'TICKET' . sprintf('%03d', $company_id) . sprintf('%03d', $countTicket + 1);

            // Collect data to store
            $data = [
                'customer_pipeline_id' => $customer_pipeline_id,
                'user_id' => $user_id,
                'company_id' => $company_id,
                'ticket_number' => $ticketNumber,
                'title' => $request->input('title'),
                'priority' => $request->input('priority'),
                'status' => 'open',
                'category' => $request->input('category'),
                'subcategory' => $request->input('subcategory'),
                'description' => $request->input('description'),
            ];

            // Store ticket data
            // result variabel will be object if data success to store
            $result = $this->ticketRepository->store($data);

            $crmAPI = new CrmAPI();

            // Check if customer pipeline id is not null
            if ($customer_pipeline_id) {
                // Update customer pipeline status to 'Komplain'
                $crmAPI->patch("crm/pipeline/status/$customer_pipeline_id", [
                    'status' => 'Komplain',
                ], $request->token);

                // Store ticket notification
                if ($notification_template_create_id) {
                    $ticketNotificationRepository->store($result->id, $notification_template_create_id);
                }

                if ($notification_template_close_id) {
                    $ticketNotificationRepository->store($result->id, $notification_template_close_id);
                }
            }

            // Upload attachment files
            // files variable will be null if no files uploaded
            // files variable will be array if files uploaded
            $files = $request->file('attachments');
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
                    $filePath = $uploadFile->save('ticket_attachments', $file);

                    // Check if app environment is production
                    if (app()->environment('production')) {
                        // Get file url from digital ocean space
                        $urlPath = config('app.do_space') . $filePath;
                    } else {
                        // Get file url from storage
                        $urlPath = url($filePath);
                    }

                    // Store ticket attachment data
                    $attachments[] = $ticketAttachmentRepository->store($result->id, $urlPath, $filePath, $fileSize, $fileType);
                }
            }

            // Add attachments data to result
            $result['attachments'] = $attachments;

            if ($customer_pipeline_id && $notification_template_create_id) {
                $crm_customer_pipeline = $crmAPI->get("crm/customer/pipeline/detail/$customer_pipeline_id");
                $customer_pipeline = $crm_customer_pipeline->data;

                // Get ticket notification template
                $notification_template_create = $icketNotificationTemplateRepository->getById($notification_template_create_id);
                
                $plugin_setting = $crmAPI->get("plugin/setting/$notification_template_create->plugin_setting_id");
                $plugin_setting_api_key = $plugin_setting->data->api_key;

                $textFormat = new TextFormat($notification_template_create->message);
                $messageFormat = $textFormat->getMessage();
                
                $textMessage = new TextMessage($messageFormat, $customer_pipeline);
                $message = $textMessage->getMessage();

                $whatsappHeaders = [
                    'api-key' => $plugin_setting_api_key,
                ];

                $crmAPI->create("whatsapp/send", [
                    'phone' => $customer_pipeline->customer->phone,
                    'message' => $message,
                ], $request->token, $whatsappHeaders);
            }
            
            return JsonResponse::success($result, "Data berhasil ditambahkan");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Change Ticket status
     * --------------------------------------------
     * Flow:
     * 1. Get id from parameter
     * 2. Get request data and validate
     * 3. Check if ticket exist by id using TicketRepository->getTicketById()
     * 4. Update Ticket status using TicketRepository->updateStatus()
     * --------------------------------------------
     *
     * @param Request $request
     * @param string $id
     * 
     * @return void
     */
    public function updateStatus(Request $request, string $id, TicketSolutionRepository $ticketSolutionRepository)
    {
        try {
            // Merge $id parameter to request data
            $request->merge(['id' => $id]);

            // Validate request data
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'status' => 'required|in:open,assigned,in progress,pending,rejected,resolved',
            ]);

            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Check if ticket exist
            // ticketExist variable will be null if ticket not exist
            // ticketExist variable will be object if ticket exist
            $ticketExist = $this->ticketRepository->getTicketById($id);

            // Return error if ticket not exist and ticket company id not equal request company id
            if (!$ticketExist || $ticketExist->company_id != $request->company_id) {
                return JsonResponse::notFound("Data tidak ditemukan");
            }

            // Update ticket status
            $result = $this->ticketRepository->updateStatus($id, $request->input('status'));

            $crmAPI = new CrmAPI();

            // Destroy ticket solution by ticket id
            $ticketSolutionRepository->destroyByTicketId($id);

            // Check if status is 'resolved'
            if ($request->input('status') === "resolved") {
                // Validate request data
                $resolvedValidator = Validator::make($request->all(), [
                    'solution' => 'required|string',
                ]);

                // Check if data is not equal validation return error
                if ($resolvedValidator->fails()) 
                {
                    $errors = $resolvedValidator->errors();
                    return JsonResponse::errorValidation($errors);
                }

                // Store ticket solution
                $ticketSolutionRepository->store([
                    'ticket_id' => $id,
                    'solution' => $request->input('solution'),
                    'nominal' => $request->input('nominal'),
                ]);

                if (count($ticketExist->notifications) > 0) {
                    $template_close = $ticketExist->notifications->where('templates.type', 'close')->first()->templates ?? null;

                    if ($template_close) {
                        $crm_customer_pipeline = $crmAPI->get("crm/customer/pipeline/detail/$ticketExist->customer_pipeline_id");
                        $customer_pipeline = $crm_customer_pipeline->data;

                        $plugin_setting = $crmAPI->get("plugin/setting/$template_close->plugin_setting_id");
                        $plugin_setting_api_key = $plugin_setting->data->api_key;

                        $textFormat = new TextFormat($template_close->message);
                        $messageFormat = $textFormat->getMessage();
                        
                        $textMessage = new TextMessage($messageFormat, $customer_pipeline);
                        $message = $textMessage->getMessage();
                    
                        $whatsappHeaders = [
                            'api-key' => $plugin_setting_api_key,
                        ];
                        
                        $crmAPI->create("whatsapp/send", [
                            'phone' => $customer_pipeline->customer->phone,
                            'message' => $message,
                        ], $request->token, $whatsappHeaders);
                    }
                }
            }
            
            return JsonResponse::success($result, "Data berhasil diubah");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Destroy Ticket data by id
     * --------------------------------------------
     * Flow:
     * 1. Get id from parameter
     * 2. Validate id
     * 3. Check if ticket exist by id using TicketRepository->getTicketById()
     * 4. Destroy ticket data by id using TicketRepository->destroy()
     * --------------------------------------------
     *
     * @param Request $request
     * @param string $id
     * 
     * @return void
     */
    public function destroy(Request $request, string $id)
    {
        try {
            // Validate request data
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer',
            ]);
    
            // Check if id is empty or not integer return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get Ticket data by id
            // result variable will be null if data not found
            // result variable will be object if data found
            $result = $this->ticketRepository->getTicketById($id);

            // Return error if data not found and company id not same
            if (!$result || $result->company_id != $request->company_id) {
                return JsonResponse::notFound("Data tidak ditemukan");
            }

            // Destroy ticket data by id
            $result = $this->ticketRepository->destroy($id);

            return JsonResponse::success($result, "Data berhasil dihapus");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Destroy batch Ticket data by list id
     * --------------------------------------------
     * Flow:
     * 1. Get request data and validate
     * 2. Looping ids
     * 3. Check if ticket exist by id using TicketRepository->getTicketById()
     * 4. Destroy batch ticket data by list id using TicketRepository->destroyBatch()
     * --------------------------------------------
     *
     * @param Request $request
     * 
     * @return void
     */
    public function destroyBatch(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'data' => 'required|array',
            ]);
    
            // Check if data is empty or not array return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get list id from request data
            $ids = $request->input('data');
            
            // Looping ids
            foreach ($ids as $id) {
                // Get Ticket data by id
                $ticket = $this->ticketRepository->getTicketById($id);

                // Return error if data not found and company id not same
                if (!$ticket || $ticket->company_id != $request->company_id)
                {
                    return JsonResponse::notFound("Data tidak ditemukan");
                }
            }

            // Destroy batch ticket data by list id
            $result = $this->ticketRepository->destroyBatch($ids);

            return JsonResponse::success($ids, "Data berhasil dihapus");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Update ticket data
     * --------------------------------------------
     * Flow:
     * 1. Get id from parameter
     * 2. Get request data and validate
     * 3. Check if ticket exist by id using TicketRepository->getTicketById()
     * 4. Update ticket data by id using TicketRepository->update()
     * --------------------------------------------
     *
     * @param Request $request
     * @param string $id
     * 
     * @return void
     */
    public function update(Request $request, string $id, UploadFileService $uploadFile, TicketAttachmentRepository $ticketAttachmentRepository)
    {
        try {
            // Merge $id parameter to request data
            $request->merge(['id' => $id]);

            // Validate request data
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'user_id' => 'required',
                'title' => 'required',
                'priority' => 'required|in:low,medium,high',
                'category' => 'required|in:product,delivery,service',
                'subcategory' => 'required',
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

            // Check if ticket exist
            $ticketExist = $this->ticketRepository->getTicketById($id);

            // Return error if ticket not exist
            if (!$ticketExist || $ticketExist->company_id != $request->company_id)
            {
                return JsonResponse::notFound("Data tidak ditemukan");
            }

            // Get attachment data
            $deleteAttachmentIds = $request->input('deleteAttachmentIds');
            $files = $request->file('newAttachments');

            // Check if deleteAttachmentIds is not null
            if ($deleteAttachmentIds) {
                // Looping ids
                foreach ($deleteAttachmentIds as $deleteAttachmentId) {
                    // Get attachment data by id
                    $attachment = $ticketAttachmentRepository->getById($deleteAttachmentId);

                    // Return error if data not found
                    if (!$attachment)
                    {
                        return JsonResponse::notFound("Data attachment tidak ditemukan");
                    }
                }

                // Delete attachment data
                $ticketAttachmentRepository->destroyBatch($deleteAttachmentIds);
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
                    $filePath = $uploadFile->save('ticket_attachments', $file);

                    // Check if app environment is production
                    if (app()->environment('production')) {
                        // Get file url from digital ocean space
                        $urlPath = config('app.do_space') . $filePath;
                    } else {
                        // Get file url from storage
                        $urlPath = url($filePath);
                    }

                    // Store ticket attachment data
                    $ticketAttachmentRepository->store($id, $urlPath, $filePath, $fileSize, $fileType);
                }
            }

            // Update ticket data
            $result = $this->ticketRepository->update($id, $request->all());

            return JsonResponse::success($result, "Data berhasil diubah");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Get ticket statictics by company id
     * --------------------------------------------
     * Flow:
     * 1. Get request data and validate
     * 2. Get company_id from request data
     * 3. Get statistics data using TicketRepository->statistics()
     * --------------------------------------------
     *
     * @param Request $request
     * 
     * @return void
     */
    public function statistics(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'company_id' => 'required',
            ]);
    
            // Check if company_id is empty return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            $company_id = $request->input('company_id');

            // Get statistics data
            $result = $this->ticketRepository->statistics($company_id);

            return JsonResponse::success($result, "Data berhasil diambil");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
