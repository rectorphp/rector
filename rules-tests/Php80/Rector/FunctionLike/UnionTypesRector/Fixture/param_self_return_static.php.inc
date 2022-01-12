<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class ParamSelfReturnStatic
{
    /**
     * @param self $arg
     * @return static
     */
    public function go(self $arg): static
    {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class ParamSelfReturnStatic
{
    public function go(self $arg): static
    {
        return $this;
    }
}

?>
