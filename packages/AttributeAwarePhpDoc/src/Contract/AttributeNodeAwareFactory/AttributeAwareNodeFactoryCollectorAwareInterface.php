<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory;

use Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactoryCollector;

interface AttributeAwareNodeFactoryCollectorAwareInterface
{
    public function setAttributeAwareNodeFactoryCollector(
        AttributeAwareNodeFactoryCollector $attributeAwareNodeFactoryCollector
    ): void;
}
