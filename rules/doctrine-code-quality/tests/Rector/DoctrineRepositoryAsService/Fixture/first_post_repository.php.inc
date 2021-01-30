<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class FirstPostRepository extends EntityRepository
{
    /**
     * Our custom method
     *
     * @return Post[]
     */
    public function findPostsByAuthor(int $authorId): array
    {
        return $this->findBy([
            'author' => $authorId
        ]);
    }
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class FirstPostRepository
{
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\Source\Entity\Post::class);
    }
    /**
     * Our custom method
     *
     * @return Post[]
     */
    public function findPostsByAuthor(int $authorId): array
    {
        return $this->repository->findBy([
            'author' => $authorId
        ]);
    }
}

?>
