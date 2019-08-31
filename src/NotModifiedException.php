<?php

namespace Divtag\LaravelEtag;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NotModifiedException extends HttpException
{
    /**
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(304, $message, $previous, [], $code);
    }
}
