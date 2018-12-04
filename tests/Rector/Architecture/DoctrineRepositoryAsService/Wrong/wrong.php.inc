<?php

namespace App\Repository;

use App\Entity\Post;
use Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityRepositoryClass;

final class FirstPostRepository extends EntityRepositoryClass
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

namespace App\Repository;

use App\Entity\Post;
use Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityRepositoryClass;

final class FirstPostRepository
{
    /**
     * @var \Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityRepositoryClass
     */
    private $repository;
    public function __construct(\Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityManagerClass $entityManager)
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
