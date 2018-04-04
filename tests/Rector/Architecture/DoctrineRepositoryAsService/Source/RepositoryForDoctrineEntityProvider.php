<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source;

use Rector\Contract\Bridge\AbstractRepositoryForDoctrineEntityProvider;

final class RepositoryForDoctrineEntityProvider extends AbstractRepositoryForDoctrineEntityProvider
{
    /**
     * @inheritdoc
     */
    public function provideRepositoriesForEntities(): array
    {
        return [
            'AppBundle\Entity\Post' => 'AppBundle\Repository\PostRepository',
        ];
    }
}
