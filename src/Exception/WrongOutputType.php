<?php

namespace GerritDrost\Ekstream\Exception;

use GerritDrost\Ekstream\Exception;

class WrongOutputType extends Exception
{
    /**
     * @var string
     */
    private $expectedType;

    /**
     * @var string
     */
    private $foundType;

    /**
     * @param string $expectedType
     * @param string $foundType
     */
    public function __construct(string $expectedType, string $foundType)
    {
        $this->expectedType = $expectedType;
        $this->foundType = $foundType;

        parent::__construct("Expected type {$expectedType} but got type {$foundType}");
    }

    /**
     * @return string
     */
    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    /**
     * @return string
     */
    public function getFoundType(): string
    {
        return $this->foundType;
    }
}