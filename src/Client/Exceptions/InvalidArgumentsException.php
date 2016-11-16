<?php

namespace Cronboy\Cronboy\Client\Exceptions;

use Exception;
use Illuminate\Contracts\Support\MessageBag;

/**
 * Class InvalidParamsArgumentsException.
 */
class InvalidArgumentsException extends Exception
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var MessageBag
     */
    private $errors;

    /**
     * InvalidArgumentsException constructor.
     *
     * @param array      $params
     * @param MessageBag $errors
     * @param int        $code
     * @param Exception  $previous
     */
    public function __construct(array $params, MessageBag $errors, $code = 422, Exception $previous = null)
    {
        parent::__construct('Wrong parameters set for cronboy request', $code, $previous);
        $this->params = $params;
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return MessageBag
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
