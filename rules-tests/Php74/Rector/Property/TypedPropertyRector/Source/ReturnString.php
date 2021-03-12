<?php

declare(strict_types=1);

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source;

final class ReturnString
{
    public function getName(): string
    {
        return 'name';
    }

    public function getNameOrNull(): ?string
    {
        if (mt_rand(0, 100)) {
            return null;
        }

        return 'name';
    }
}
