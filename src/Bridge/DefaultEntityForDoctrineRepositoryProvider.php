<?php declare(strict_types=1);

namespace Rector\Bridge;

use Nette\Utils\Strings;
use Rector\Contract\Bridge\EntityForDoctrineRepositoryProviderInterface;

final class DefaultEntityForDoctrineRepositoryProvider implements EntityForDoctrineRepositoryProviderInterface
{
    public function provideEntityForRepository(string $repository): ?string
    {
        // "SomeRepository" => "Some"
        $withoutSuffix = substr($repository, 0, - strlen('Repository'));

        // "App\Repository\Some" => "App\Entity\Some"
        return Strings::replace($withoutSuffix, '#Repository#', 'Entity');
    }
}
