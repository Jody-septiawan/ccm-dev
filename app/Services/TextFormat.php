<?php
namespace App\Services;

class TextFormat
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
        $this->message = $this->splitMessage("---", $this->message);
        $this->replaceMessage();
    }

    public function splitMessage($params, $message)
    {
        $message = explode($params, $message);

        return $message[array_rand($message)];
    }

    public function replaceMessage()
    {
        $messages = preg_split('/({[^}]*[^\/]})/i', $this->message, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        
        $message = "";
        foreach ($messages as $text) {
            $message .= $this->splitMessage('|', $text);
        }

        $message = str_replace("}", "", $message);
        $message = str_replace("{", "", $message);
        $message = str_replace("  ", " ", $message);

        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
