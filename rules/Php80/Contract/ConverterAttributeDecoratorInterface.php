<?php

declare (strict_types=1);
namespace Rector\Php80\Contract;

use PhpParser\Node\Attribute;
interface ConverterAttributeDecoratorInterface
{
    public function getAttributeName() : string;
    public function decorate(Attribute $attribute) : void;
}
