<?php declare(strict_types=1);

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
