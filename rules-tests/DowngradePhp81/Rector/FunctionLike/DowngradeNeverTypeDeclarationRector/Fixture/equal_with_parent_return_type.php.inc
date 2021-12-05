<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

abstract class SomeParent
{
    public abstract function run(): never;
}

class EqualWithParentReturnType extends SomeParent {
    public function run(): never
    {
        throw new \Exception('test');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

abstract class SomeParent
{
    /**
     * @return never
     */
    public abstract function run();
}

class EqualWithParentReturnType extends SomeParent {
    /**
     * @return never
     */
    public function run()
    {
        throw new \Exception('test');
    }
}

?>
