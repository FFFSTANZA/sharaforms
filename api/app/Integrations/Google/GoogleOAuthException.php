<?php

namespace App\Integrations\Google;

use RuntimeException;

class GoogleOAuthException extends RuntimeException
{
    public const MISSING_REFRESH_TOKEN = 'missing_refresh_token';

    public const NETWORK_ERROR = 'network_error';

    public const INVALID_GRANT = 'invalid_grant';

    public function __construct(
        string $message,
        protected string $reason,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function reason(): string
    {
        return $this->reason;
    }
}