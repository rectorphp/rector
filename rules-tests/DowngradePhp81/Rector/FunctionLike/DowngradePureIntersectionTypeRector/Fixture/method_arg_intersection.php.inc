<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

final class SomeClass
{
    public function run(Bar&\Qux $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

final class SomeClass
{
    /**
     * @param \Baz\Bar&\Qux $value
     */
    public function run($value)
    {
    }
}

?>
