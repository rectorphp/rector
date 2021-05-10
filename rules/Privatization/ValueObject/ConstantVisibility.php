<?php

declare(strict_types=1);

namespace Rector\Privatization\ValueObject;

final class ConstantVisibility
{
    public function __construct(
        private bool $isPublic,
        private bool $isProtected,
        private bool $isPrivate
    ) {
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function isProtected(): bool
    {
        return $this->isProtected;
    }

    public function isPrivate(): bool
    {
        return $this->isPrivate;
    }
}
