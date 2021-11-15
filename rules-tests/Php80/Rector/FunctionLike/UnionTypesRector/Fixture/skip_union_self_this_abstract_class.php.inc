<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

abstract class SkipUnionSelfThisAbstractClass
{
    /**
     * @return $this
     */
    public function withFromId(int $fromId): self
    {
        return $this;
    }
}
