<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source;

use Rector\Contract\Bridge\DoctrineEntityAndRepositoryMapperInterface;

final class EntityForDoctrineRepositoryProvider implements DoctrineEntityAndRepositoryMapperInterface
{
    /**
     * @var string[]
     */
    private $map = [
        'App\Repository\PostRepository' => 'App\Entity\Post',
    ];

    public function mapRepositoryToEntity(string $name): ?string
    {
        return $this->map[$name] ?? null;
    }
}
