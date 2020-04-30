<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Collector;

final class PlaceholderToValueCollector
{
    /**
     * @var string[]
     */
    private $placeholderToValue = [];

    public function add(string $placeholder, string $value): void
    {
        // space hacking to deminish Nop() indents
        $this->placeholderToValue[$placeholder . PHP_EOL] = $value;
    }

    /**
     * @return string[]
     */
    public function getMap(): array
    {
        return $this->placeholderToValue;
    }
}
