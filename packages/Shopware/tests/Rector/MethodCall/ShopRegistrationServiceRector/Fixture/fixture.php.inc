<?php

namespace Rector\Shopware\Tests\Rector\MethodCall\ShopRegistrationServiceRector\Fixture;

class SomeClass
{
    public function run()
    {
        $shop = new \Shopware\Models\Shop\Shop();
        $shop->registerResources();
    }
}

?>
-----
<?php

namespace Rector\Shopware\Tests\Rector\MethodCall\ShopRegistrationServiceRector\Fixture;

class SomeClass
{
    public function run()
    {
        $shop = new \Shopware\Models\Shop\Shop();
        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);
    }
}

?>
