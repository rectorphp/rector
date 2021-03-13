<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
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

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class FirstPostRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post>
     */
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\Entity\Post::class);
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
