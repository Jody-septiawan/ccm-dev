<?php
namespace App\Services;

use App\Services\ExternalAPIs\CrmAPI;

class TextMessage
{
    public $message;

    /**
     * TextMessage constructor.
     *
     * @param string $message
     * @param object $customer_pipeline
     */
    public function __construct(string $message,object $customer_pipeline)
    {
        $this->message = $message;
        $this->replaceMessage($customer_pipeline);
    }

    /**
     * Replace message
     *
     * @param object $customer_pipeline
     */
    private function replaceMessage(object $customer_pipeline)
    {
        $customerName = $customer_pipeline->customer->name;
        $invoice = $customer_pipeline->external_id;
        $resi = $customer_pipeline->awb;

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