<?php

namespace River\Exception;

use River\Exception;

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