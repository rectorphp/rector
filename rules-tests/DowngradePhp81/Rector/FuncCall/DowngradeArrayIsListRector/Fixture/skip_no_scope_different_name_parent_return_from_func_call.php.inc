<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\Fixture;

trait ServiceLocatorTrait
{
    private array $factories;
    private array $loading = [];
    private array $providedTypes;

    public function get(string $id): mixed
    {
        if (!isset($this->factories[$id])) {
            throw $this->createNotFoundException($id);
        }

        $this->loading[$id] = $id;
        try {
            return $this->factories[$id]($this);
        } finally {
            unset($this->loading[$id]);
        }
    }
}
