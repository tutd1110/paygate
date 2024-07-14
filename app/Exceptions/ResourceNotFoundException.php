<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ResourceNotFoundException extends HttpException
{
    /**
     * @param string|null     $message  The internal exception message
     * @param Throwable|null $previous The previous exception
     * @param int             $code     The internal exception code
     */
    public function __construct(?string $message = '', Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (null === $message) {
            trigger_deprecation('symfony/http-kernel', '5.3', 'Passing null as $message to "%s()" is deprecated, pass an empty string instead.', __METHOD__);

            $message = '';
        }

        parent::__construct(406, $message, $previous, $headers, $code);
    }
}
