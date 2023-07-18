<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TicketNotificationTemplateRepository;
use Illuminate\Support\Facades\Validator;
use App\Libs\Json\JsonResponse;
use App\Services\ExternalAPIs\CrmAPI;

class TicketNotificationTemplateController extends Controller
{
    private $ticketNotificationTemplateRepository;

    public function __construct(TicketNotificationTemplateRepository $ticketNotificationTemplateRepository)
    {
        $this->ticketNotificationTemplateRepository = $ticketNotificationTemplateRepository;
    }

    /**
     * Display a listing of the ticket notification template.
     * ---------------------------------------------------
     * Flow:
     * 1. Get all ticket notification template
     * ---------------------------------------------------
     *
     * @param Request $request
     * 
     * @return void
     */
    public function all(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'type' => 'in:create,close',
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get all ticket notification template
            $ticketTemplates = $this->ticketNotificationTemplateRepository->getDatatable($request);

            $crmAPI = new CrmAPI();

            foreach ($ticketTemplates as $item) {
                $plugin_setting = $crmAPI->get("plugin/setting/$item->plugin_setting_id");
                $item->plugin_setting = $plugin_setting->data;
            }

            return $ticketTemplates;
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Display a data ticket notification template by id.
     * ---------------------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Check if data is not equal validation return error
     * 3. Get ticket notification template by id
     * 4. Check if ticket notification template is not found return error
     * 5. Return ticket notification template
     * ---------------------------------------------------
     *
     * @param Request $request
     * @param string $id
     * 
     * @return void
     */
    public function show(Request $request, string $id)
    {
        try {
            // Validate request data
            $validator = Validator::make(['template_id' => $id], [
                'template_id' => 'required'
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get ticket notification template by id
            $ticketTemplate = $this->ticketNotificationTemplateRepository->getById($id);

            if (!$ticketTemplate || $ticketTemplate->company_id != $request->company_id) {
                return JsonResponse::notFound("Data tidak ditemukan");
            }

            $crmAPI = new CrmAPI();
    
            // Get plugin setting
            $plugin_setting = $crmAPI->get("plugin/setting/$ticketTemplate->plugin_setting_id");
            $ticketTemplate->plugin_setting = $plugin_setting->data;

            return JsonResponse::success($ticketTemplate);
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Store a newly created ticket notification template.
     * ---------------------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Check if data is not equal validation return error
     * 3. Store ticket notification template
     * ---------------------------------------------------
     *
     * @param Request $request
     * 
     * @return void
     */
    public function store(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'plugin_setting_id' => 'required',
                'name' => 'required',
                'message' => 'required',
                'type' => 'required|in:create,close',
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Store ticket notification template
            $ticketNotificationTemplate = $this->ticketNotificationTemplateRepository->store($request->all());

            return JsonResponse::success($ticketNotificationTemplate, "Data berhasil ditambahkan");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Update ticket notification template by id.
     * ---------------------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Check if data is not equal validation return error
     * 3. Update ticket notification template
     * ---------------------------------------------------
     *
     * @param Request $request
     * @param string $id
     * 
     * @return void
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'plugin_setting_id' => 'required',
                'name' => 'required',
                'message' => 'required',
                'type' => 'required|in:create,close',
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get ticket notification template by id
            $ticketTemplate = $this->ticketNotificationTemplateRepository->getById($id);

            if (!$ticketTemplate || $ticketTemplate->company_id != $request->company_id)
            {
                return JsonResponse::notFound();
            }

            // Update ticket notification template
            $ticketNotificationTemplate = $this->ticketNotificationTemplateRepository->update($id, $request->all());

            return JsonResponse::success($ticketNotificationTemplate, "Data berhasil diubah");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Delete ticket notification template by id.
     * ---------------------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Check if data is not equal validation return error
     * 3. Delete ticket notification template
     * ---------------------------------------------------
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
            $validator = Validator::make(['template_id' => $id], [
                'template_id' => 'required'
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Get ticket notification template by id
            $ticketTemplate = $this->ticketNotificationTemplateRepository->getById($id);

            if (!$ticketTemplate || $ticketTemplate->company_id != $request->company_id)
            {
                return JsonResponse::notFound();
            }

            // Delete ticket notification template
            $ticketNotificationTemplate = $this->ticketNotificationTemplateRepository->destroy($id);

            return JsonResponse::success($ticketNotificationTemplate, "Data berhasil dihapus");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }

    /**
     * Destroy batch ticket notification template by id.
     * ---------------------------------------------------
     * Flow:
     * 1. Validate request data
     * 2. Check if data is not equal validation return error
     * 3. Delete ticket notification template
     * ---------------------------------------------------
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
                'data' => 'required|array'
            ]);
            
            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Check if ticket notification template is exist
            foreach ($request->data as $dataId) {
                $ticketTemplate = $this->ticketNotificationTemplateRepository->getById($dataId);

                if (!$ticketTemplate)
                {
                    return JsonResponse::notFound();
                }
            }

            // Delete ticket notification template
            $ticketNotificationTemplate = $this->ticketNotificationTemplateRepository->destroyBatch($request->data);

            return JsonResponse::success($request->data, "Data berhasil dihapus");
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
