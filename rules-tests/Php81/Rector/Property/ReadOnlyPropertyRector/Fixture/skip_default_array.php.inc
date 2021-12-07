<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipDefaultArray
{
    /**
     * @var Post[]
     */
    private array $posts = [];

    public function __construct(PostDataProvider $postDataProvider)
    {
        $this->posts = $postDataProvider->provide();
    }
}
