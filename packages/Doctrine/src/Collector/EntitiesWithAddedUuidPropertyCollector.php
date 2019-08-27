<?php declare(strict_types=1);

namespace Rector\Doctrine\Collector;

final class EntitiesWithAddedUuidPropertyCollector
{
    /**
     * @var string[]
     */
    private $classes = [];

    public function addClass(string $class): void
    {
        $this->classes[] = $class;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
