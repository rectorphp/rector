<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions\Source;

final class SomeValueObject
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
