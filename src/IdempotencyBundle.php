<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class IdempotencyBundle extends Bundle
{
    public function getPath(): string
    {
        return __DIR__;
    }
}
