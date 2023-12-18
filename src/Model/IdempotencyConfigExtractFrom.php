<?php
declare(strict_types=1);

namespace Conejerock\IdempotencyBundle\Model;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;


class IdempotencyConfigExtractFrom
{
    public function __construct(
        private string $scope,
        private string $location,
        private bool   $mandatory,
    )
    {
    }

    public static function fromValues(array $values): self
    {
        return new self(
            $values['scope'],
            $values['location'],
            (bool)$values['mandatory'],
        );
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function extractValue(Request $request): ?string
    {

        $data = match ($this->getScope()) {
            'body' => $request->request->get($this->getLocation()),
            'query' => $request->query->get($this->getLocation()),
            'headers' => $request->headers->get($this->getLocation()),
        };
        return $data;
//        $keys = explode('.', $this->getLocation());

//        return array_reduce($keys, function ($carry, $key) use ($data) {
//            return is_array($carry) && array_key_exists($key, $carry) ? $carry[$key] : null;
//        }, $data);

    }
}
