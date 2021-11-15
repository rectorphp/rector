<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

interface SkipUnionSelfThisInterface
{
    /**
     * @return $this
     */
    public function withFromId(int $fromId): self;
}
