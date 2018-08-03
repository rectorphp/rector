<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;

// @todo this class is analysed twice by PHPStan and thus it fails - restart it!
final class Post_Repository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
    }
}
