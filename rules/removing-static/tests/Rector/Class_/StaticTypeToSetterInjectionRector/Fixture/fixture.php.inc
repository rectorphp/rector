<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory;

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

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory;

final class CheckoutEntityFactory
{
    /**
     * @var \Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory
     */
    private $genericEntityFactory;
    public function setGenericEntityFactory(\Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactory $genericEntityFactory): void
    {
        $this->genericEntityFactory = $genericEntityFactory;
    }
    public function run()
    {
        return $this->genericEntityFactory->make();
    }
}

?>
