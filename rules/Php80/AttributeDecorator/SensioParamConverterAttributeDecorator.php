<?php

declare (strict_types=1);
namespace Rector\Php80\AttributeDecorator;

use PhpParser\Node\Attribute;
use Rector\Php80\Contract\AttributeDecoratorInterface;
final class SensioParamConverterAttributeDecorator implements AttributeDecoratorInterface
{
    public function getAttributeName() : string
    {
        return 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter';
    }
    public function decorate(Attribute $attribute) : void
    {
        // make first named arg silent, @see https://github.com/rectorphp/rector/issues/7352
        $firstArg = $attribute->args[0];
        $firstArg->name = null;
    }
}
