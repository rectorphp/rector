<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory;

use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;

interface AttributeAwareNodeFactoryAwareInterface
{
    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void;
}
