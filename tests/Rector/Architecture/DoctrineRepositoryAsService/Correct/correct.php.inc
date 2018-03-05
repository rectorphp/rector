<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class PostRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
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
