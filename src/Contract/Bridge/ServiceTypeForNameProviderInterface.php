<?php declare(strict_types=1);

namespace Rector\Contract\Bridge;

interface ServiceTypeForNameProviderInterface
{
    public function provideTypeForName(string $name): ?string;
}
