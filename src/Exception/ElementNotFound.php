<?php

namespace GerritDrost\Ekstream\Exception;

use GerritDrost\Ekstream\Exception;

class ElementNotFound extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = 'The requested element could not be found')
    {
        parent::__construct($message);
    }
}