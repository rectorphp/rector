<?php

namespace Rector\Shopware\Tests\Rector\ClassConstFetch\ShopwareVersionConstsRector\Fixture;

class SomeClass
{
    public function run()
    {
        echo \Shopware::VERSION;
    }
}

?>
-----
<?php

namespace Rector\Shopware\Tests\Rector\ClassConstFetch\ShopwareVersionConstsRector\Fixture;

class SomeClass
{
    public function run()
    {
        echo Shopware()->Container()->getParameter('shopware.release.version');
    }
}

?>
