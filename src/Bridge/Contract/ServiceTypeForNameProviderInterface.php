<?php declare(strict_types=1);

namespace Rector\Bridge\Contract;

interface ServiceTypeForNameProviderInterface
{
    public function provideTypeForName(string $name): ?string;
}
