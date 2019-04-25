<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Tests\Rector\Class_Fixture\EntityFactoryInsideEntityFactoryRector\Source;

interface GenericEntityFactoryAwareInterface
{
    public function setEntityFactory(GenericEntityFactory $genericEntityFactory);
}
