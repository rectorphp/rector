<?php

namespace Rector\Oxid\Tests\Rector\FuncCall\OxidReplaceBackwardsCompatabilityClassRector\Fixture;

class SomeClass
{
    public function run()
    {
        oxNew('oxcmp_basket');
        oxNew('oxcmp_categories', null, 'arg2');
        oxNew('oxreverseproxyurlpartstoflush');
    }
}

?>
-----
<?php

namespace Rector\Oxid\Tests\Rector\FuncCall\OxidReplaceBackwardsCompatabilityClassRector\Fixture;

class SomeClass
{
    public function run()
    {
        oxNew(\OxidEsales\Eshop\Application\Component\BasketComponent::class);
        oxNew(\OxidEsales\Eshop\Application\Component\CategoriesComponent::class, null, 'arg2');
        oxNew(\OxidEsales\Eshop\Core\Cache\ReverseProxy\ReverseProxyUrlPartsToFlush::class);
    }
}

?>
