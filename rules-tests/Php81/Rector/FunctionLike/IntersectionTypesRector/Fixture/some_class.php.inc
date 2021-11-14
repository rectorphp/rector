<?php

namespace Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Fixture;

final class SomeClass
{
    /**
     * @param string&int $types
     */
    public function process($types)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\Fixture;

final class SomeClass
{
    /**
     * @param string&int $types
     */
    public function process(int&string $types)
    {
    }
}

?>
