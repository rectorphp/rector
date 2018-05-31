<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Post;
use Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityRepositoryClass;

final class PostRepository
{
    /**
     * @var \Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityRepositoryClass
     */
    private $repository;
    public function __construct(\Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\EntityManagerClass $entityManager)
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
