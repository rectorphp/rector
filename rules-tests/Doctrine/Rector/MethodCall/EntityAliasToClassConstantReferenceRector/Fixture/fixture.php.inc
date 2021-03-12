<?php

namespace Rector\Tests\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector\Fixture;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

final class PostRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository('App:Post');
    }
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector\Fixture;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

final class PostRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
    }
}

?>
