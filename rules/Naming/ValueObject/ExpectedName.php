<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

final class ExpectedName
{
    public function __construct(
        private string $name,
        private string $singularized
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSingularized(): string
    {
        return $this->singularized;
    }

    public function isSingular(): bool
    {
        return $this->name === $this->singularized;
    }
}
