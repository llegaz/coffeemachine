<?php

namespace LLegaz\CoffeeMachine\Exception;

class CoffeeMachineException extends \Exception
{
    public function __construct(string $message = 'CoffeeMachine blows off and is not responding' . PHP_EOL, int $code = 1337, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
