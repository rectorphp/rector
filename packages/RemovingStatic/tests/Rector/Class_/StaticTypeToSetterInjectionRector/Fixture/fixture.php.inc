<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Fixture;

use Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source\GenericEntityFactory;

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

namespace Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Fixture;

use Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source\GenericEntityFactory;

final class CheckoutEntityFactory implements \Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source\GenericEntityFactoryAwareInterface
{
    /**
     * @var \Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source\GenericEntityFactory
     */
    private $genericEntityFactory;
    public function setGenericEntityFactory(\Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source\GenericEntityFactory $genericEntityFactory): void
    {
        $this->genericEntityFactory = $genericEntityFactory;
    }
    public function run()
    {
        return $this->genericEntityFactory->make();
    }
}

?>
