<?php

declare (strict_types=1);
namespace Rector\Php80\Contract;

use PhpParser\Node\Attribute;
interface AttributeDecoratorInterface
{
    public function getAttributeName() : string;
    public function decorate(Attribute $attribute) : void;
}
