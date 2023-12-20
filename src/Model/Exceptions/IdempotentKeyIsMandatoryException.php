<?php

declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Model\Exceptions;

use Conejerock\IdempotencyBundle\Model\IdempotencyConfig;

class IdempotentKeyIsMandatoryException extends \Exception
{
    public function __construct(
        IdempotencyConfig $config
    ) {
        parent::__construct(
            sprintf(
                '[Idempotency-bundle] Key "%s" in "%s" scope is mandatory',
                $config->getLocation(),
                $config->getScope()
            )
        );
    }
}
