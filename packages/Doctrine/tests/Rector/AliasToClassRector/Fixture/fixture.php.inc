<?php

namespace App\Repository;

use App\Entity\Post;
use Rector\Doctrine\Tests\Rector\AliasToClassRector\Source\DoctrineEntityManager;

final class PostRepository
{
    /**
     * @var DoctrineEntityManager
     */
    private $repository;
    public function __construct(DoctrineEntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository('App:Post');
    }
}

?>
-----
<?php

namespace App\Repository;

use App\Entity\Post;
use Rector\Doctrine\Tests\Rector\AliasToClassRector\Source\DoctrineEntityManager;

final class PostRepository
{
    /**
     * @var DoctrineEntityManager
     */
    private $repository;
    public function __construct(DoctrineEntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
    }
}

?>
