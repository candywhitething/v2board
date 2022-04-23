<?php

namespace App\Exceptions;

/**
 * Class TelegramException
 */
class TelegramException extends \Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}