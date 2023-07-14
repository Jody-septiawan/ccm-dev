<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TicketScoreRepository;
use App\Repositories\TicketRepository;
use App\Repositories\RatingRepository;
use Illuminate\Support\Facades\Validator;
use App\Libs\Json\JsonResponse;

class TicketScoreController extends Controller
{
    private $ticketScoreRepository;

    public function __construct(TicketScoreRepository $ticketScoreRepository)
    {
        $this->ticketScoreRepository = $ticketScoreRepository;
    }

    /**
    * Store Ticket rating
    * --------------------------------------
    * Flow:
    * 1. Validate request data
    * 2. Check if data is not equal validation return error
    * 3. Get rating by value
    * 4. Check if rating not found return error
    * 5. Store ticket score
    * --------------------------------------
    *
    * @param Request $request
    * @param string $id
    * @param TicketRepository $ticketRepository
    * @param RatingRepository $ratingRepository
    
    * @return void
    */
    public function store(Request $request, string $id, TicketRepository $ticketRepository, RatingRepository $ratingRepository)
    {
        try {
            // Merge $id to request
            $request->merge(['ticket_id' => $id]);

            // Validate request data
            $validator = Validator::make($request->all(), [
                'ticket_id' => 'required',
                'rating' => 'required',
            ]);

            // Check if data is not equal validation return error
            if ($validator->fails()) 
            {
                $errors = $validator->errors();
                return JsonResponse::errorValidation($errors);
            }

            // Check if ticket not found return error
            $ticket = $ticketRepository->getTicketById($id);

            if (!$ticket) 
            {
                return JsonResponse::notFound('Ticket not found');
            }

            // Check if rating not found return error
            $rating = $ratingRepository->getByValue($request->rating);

            if (!$rating) 
            {
                return JsonResponse::notFound('Rating not found');
            }

            $ticketScoreExist = $this->ticketScoreRepository->getByTicketId($ticket->id);

            if ($ticketScoreExist)
            {
                return JsonResponse::badRequest('Ticket score already exist');
            }

            $request->merge(['rating_id' => $rating->id]);
            $request->merge(['ticket_id' => $ticket->id]);

            $result = $this->ticketScoreRepository->store($request->all());

            return JsonResponse::success($result, 'Data berhasil ditambahkan');
        } catch (Throwable $th) {
            return JsonResponse::error($th->getMessage()); 
        }
    }
}
