<?php

namespace Rector\Architecture\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use App\Entity\Post;
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

    public function shouldSkip()
    {
        $anotherClass = new \stdClass();
        $anotherClass->findBy();
    }
}

?>
-----
<?php

namespace Rector\Architecture\Tests\Rector\DoctrineRepositoryAsService\Fixture;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class FirstPostRepository
{
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\FirstPost::class);
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

    public function shouldSkip()
    {
        $anotherClass = new \stdClass();
        $anotherClass->findBy();
    }
}

?>
