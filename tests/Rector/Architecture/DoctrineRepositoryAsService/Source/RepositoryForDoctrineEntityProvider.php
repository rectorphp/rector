<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source;

use Rector\Contract\Bridge\RepositoryForDoctrineEntityProviderInterface;

final class RepositoryForDoctrineEntityProvider implements RepositoryForDoctrineEntityProviderInterface
{
    /**
     * @var string[]
     */
    private $map = [
        'AppBundle\Entity\Post' => 'AppBundle\Repository\PostRepository',
    ];

    public function provideRepositoryForEntity(string $name): ?string
    {
        return $this->map[$name] ?? null;
    }
}
