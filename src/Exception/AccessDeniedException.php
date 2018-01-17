<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Exception;

use Throwable;

class AccessDeniedException extends HttpException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('The request with specified parameters is not allowed. Please verify token or single-time token, or other authorization method.', Codes::HTTP_FORBIDDEN, $previous);
    }
}
