<?php

/**
 * Mock Registrar_Exception for testing
 * 
 * This mock class simulates FOSSBilling's Registrar_Exception
 * for unit testing without requiring the full FOSSBilling installation.
 */
class Registrar_Exception extends Exception
{
    /**
     * Create a new Registrar Exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
