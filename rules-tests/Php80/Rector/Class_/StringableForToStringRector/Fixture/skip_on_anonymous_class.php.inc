<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\StringableForToStringRector\Fixture;

class SkipOnAnonymousClass
{
    public function setFault(): void
    {
        (string) (new class {
            public function __toString(): string
            {
                return (string) $this;
            }
        });
    }
}
