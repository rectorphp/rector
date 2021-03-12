<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Fixture;

use App\Entity\Post;
use Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\Source\RandomClass;

final class SkipWithoutParentClass extends RandomClass
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
