<?php declare(strict_types=1);

namespace Rector\Contract\Bridge;

interface EntityForDoctrineRepositoryProviderInterface
{
    public function provideEntityForRepository(string $name): ?string;
}
