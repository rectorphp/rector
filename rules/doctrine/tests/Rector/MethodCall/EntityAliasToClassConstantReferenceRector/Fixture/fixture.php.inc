<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\EntityAliasToClassConstantReferenceRector\Fixture;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

final class Fixture
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

namespace Rector\Doctrine\Tests\Rector\MethodCall\EntityAliasToClassConstantReferenceRector\Fixture;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

final class Fixture
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
