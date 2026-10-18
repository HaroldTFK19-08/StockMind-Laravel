<?php

namespace App\Modules\Shared\Exceptions;

use Exception;

class ReglasNegocioExceptions extends Exception
{
    public function __construct(string $message, private int $status = 422)
    {
        parent::__construct($message);
    }
    public function render($request)
    {
        return response()->json(['message' => $this->getMessage()], $this->status);
    }
}
