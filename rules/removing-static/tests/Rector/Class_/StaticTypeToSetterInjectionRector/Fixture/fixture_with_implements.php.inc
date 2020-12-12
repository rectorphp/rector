<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\StaticTypeToSetterInjectionRector\Source\GenericEntityFactoryWithInterface;

final class TheSameButWithImplements
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

final class TheSameButWithImplements implements \ParentSetterEnforcingInterface
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
