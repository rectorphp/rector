<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipUnionSelfThisFinalClass
{
    /**
     * @return $this
     */
    public function withFromId(int $fromId): self
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

final class SkipUnionSelfThisFinalClass
{
    public function withFromId(int $fromId): self
    {
        return $this;
    }
}

?>
