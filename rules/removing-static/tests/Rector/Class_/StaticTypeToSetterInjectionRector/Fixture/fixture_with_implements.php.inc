<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface;

final class FixtureWithImplements
{
    public function run()
    {
        return GenericEntityFactoryWithInterface::make();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface;

final class FixtureWithImplements implements \ParentSetterEnforcingInterface
{
    /**
     * @var \Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface
     */
    private $genericEntityFactoryWith;
    public function setGenericEntityFactoryWith(\Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface $genericEntityFactoryWith): void
    {
        $this->genericEntityFactoryWith = $genericEntityFactoryWith;
    }
    public function run()
    {
        return $this->genericEntityFactoryWith->make();
    }
}

?>
