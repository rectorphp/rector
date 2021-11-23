<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

final class SomeClass
{
    public function run(): Bar&\Qux
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
     * @return \Baz\Bar&\Qux
     */
    public function run()
    {
    }
}

?>
