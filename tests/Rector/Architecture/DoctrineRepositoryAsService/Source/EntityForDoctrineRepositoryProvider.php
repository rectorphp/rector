<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source;

use Rector\Contract\Bridge\EntityForDoctrineRepositoryProviderInterface;

final class EntityForDoctrineRepositoryProvider implements EntityForDoctrineRepositoryProviderInterface
{
    /**
     * @var string[]
     */
    private $map = [
        'App\Repository\PostRepository' => 'App\Entity\Post',
    ];

    public function provideEntityForRepository(string $name): ?string
    {
        return $this->map[$name] ?? null;
    }
}
