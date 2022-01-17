<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class SkipUnionStaticThisClass
{
    /**
     * @return $this
     */
    public function withFromId(int $fromId): static
    {
        return $this;
    }
}
