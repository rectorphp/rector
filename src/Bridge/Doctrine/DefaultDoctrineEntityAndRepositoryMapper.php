<?php declare(strict_types=1);

namespace Rector\Bridge\Doctrine;

use Nette\Utils\Strings;
use Rector\Bridge\Contract\DoctrineEntityAndRepositoryMapperInterface;
use function Safe\substr;

final class DefaultDoctrineEntityAndRepositoryMapper implements DoctrineEntityAndRepositoryMapperInterface
{
    public function mapRepositoryToEntity(string $repository): ?string
    {
        // "SomeRepository" => "Some"
        $withoutSuffix = substr($repository, 0, - strlen('Repository'));

        // "App\Repository\Some" => "App\Entity\Some"
        return Strings::replace($withoutSuffix, '#Repository#', 'Entity');
    }

    public function mapEntityToRepository(string $entity): ?string
    {
        // "Some" => "SomeRepository"
        $withSuffix = $entity . 'Repository';

        // "App\Entity\SomeRepository" => "App\Repository\SomeRepository"
        return Strings::replace($withSuffix, '#Entity#', 'Repository');
    }
}
