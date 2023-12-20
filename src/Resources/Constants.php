<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Resources;

interface Constants
{
    public const PREFIX_INNER_IDEMPOTENT_KEY = 'inner-idempotent-cached-key';

    public const X_HEADER_CACHED_REQUEST = 'x-idempotent-cached-request';
}
