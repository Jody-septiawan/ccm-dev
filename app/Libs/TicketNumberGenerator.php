<?php

namespace App\Libs;

class TicketNumberGenerator
{
    public static function generate($company_id, $countTicket)
    {
        // Generate ticket number with format TICKET{company_id}{count_ticket}
        return 'TICKET' . sprintf('%03d', $company_id) . sprintf('%03d', $countTicket);
    }
}
