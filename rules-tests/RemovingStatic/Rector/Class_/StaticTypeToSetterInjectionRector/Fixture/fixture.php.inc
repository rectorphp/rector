<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory;

final class CheckoutEntityFactory
{
    public function run()
    {
        return GenericEntityFactory::make();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory;

final class CheckoutEntityFactory
{
    /**
     * @var \Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory
     */
    private $genericEntityFactory;
    public function setGenericEntityFactory(\Rector\Tests\RemovingStatic\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory $genericEntityFactory): void
    {
        $this->genericEntityFactory = $genericEntityFactory;
    }
    public function run()
    {
        return $this->genericEntityFactory->make();
    }
}

?>
