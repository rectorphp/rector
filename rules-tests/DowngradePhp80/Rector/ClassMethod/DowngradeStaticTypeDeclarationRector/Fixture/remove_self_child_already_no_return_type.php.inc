<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

interface RemoveSelfChildAlreadyNoType
{
    public function run(): self;
}

class Child implements RemoveSelfChildAlreadyNoType
{
    // return type may removed by other rules
    // @see https://github.com/rectorphp/rector/issues/6652
    public function run()
    {

    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector\Fixture;

interface RemoveSelfChildAlreadyNoType
{
    /**
     * @return $this
     */
    public function run();
}

class Child implements RemoveSelfChildAlreadyNoType
{
    // return type may removed by other rules
    // @see https://github.com/rectorphp/rector/issues/6652
    public function run()
    {

    }
}

?>
