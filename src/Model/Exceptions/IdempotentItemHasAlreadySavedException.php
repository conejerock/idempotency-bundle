<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Model\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IdempotentItemHasAlreadySavedException extends BadRequestHttpException
{
    public function __construct(
        private string $idCache
    )
    {
        parent::__construct();
    }

    public function getIdCache(): string
    {
        return $this->idCache;
    }
}
