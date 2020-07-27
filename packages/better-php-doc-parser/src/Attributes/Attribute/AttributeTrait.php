<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Attribute;

trait AttributeTrait
{
    /**
     * @var mixed[]
     */
    private $attributes = [];

    /**
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }
}
