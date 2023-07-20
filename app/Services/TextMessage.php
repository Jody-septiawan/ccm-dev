<?php
namespace App\Services;

use App\Services\ExternalAPIs\CrmAPI;
use Illuminate\Support\Facades\Config;

class TextMessage
{
    public $message;

    /**
     * TextMessage constructor.
     *
     * @param string $message
     * @param object $customer_pipeline
     * @param object $ticket
     */
    public function __construct(string $message,object $customer_pipeline, object $ticket)
    {
        $this->message = $message;
        $this->replaceMessage($customer_pipeline, $ticket);
    }

    /**
     * Replace message
     *
     * @param object $customer_pipeline
     * @param object $ticket
     */
    private function replaceMessage(object $customer_pipeline, object $ticket)
    {
        $customerName = $customer_pipeline->customer->name;
        $invoice = $customer_pipeline->external_id;
        $resi = $customer_pipeline->awb;
        $ticketUrl = Config::get('crm_gabungin.api_url') . '/ticket/' . $ticket->ticket_number;

        $with_customer = $this->isContainData('customer.', $this->message);
        if ($with_customer) {
            $this->message = str_replace("%customer.name%", $customerName ?? "-", $this->message);
        }

        $with_invoice = $this->isContainData('invoice', $this->message);
        if ($with_invoice) {
            $this->message = str_replace("%invoice%", $invoice ?? "-", $this->message);
        }

        $with_resi = $this->isContainData('resi', $this->message);
        if ($with_resi) {
            $this->message = str_replace("%resi%", $resi ?? "-", $this->message);
        }

        $with_ticket = $this->isContainData('ticket.', $this->message);
        if ($with_ticket) {
            $this->message = str_replace("%ticket.url%", $ticketUrl ?? "-", $this->message);
        }
    }

    /**
     * Check if message contain data
     *
     * @param string $parsed_key
     * @param string $parsed_str
     * 
     * @return boolean
     */
    private function isContainData(string $parsed_key, string  $parsed_str)
    {
        if (preg_match("/{$parsed_key}/", strtolower($parsed_str))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get message
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }
}