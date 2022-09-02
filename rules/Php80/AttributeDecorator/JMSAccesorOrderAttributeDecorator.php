<?php

declare (strict_types=1);
namespace Rector\Php80\AttributeDecorator;

use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use Rector\Php80\Contract\AttributeDecoratorInterface;
final class JMSAccesorOrderAttributeDecorator implements AttributeDecoratorInterface
{
    public function getAttributeName() : string
    {
        return 'JMS\\Serializer\\Annotation\\AccessorOrder';
    }
    public function decorate(Attribute $attribute) : void
    {
        // make first named arg explicit, @see https://github.com/rectorphp/rector/issues/7369
        $firstArg = $attribute->args[0];
        if ($firstArg->name !== null) {
            return;
        }
        $firstArg->name = new Identifier('order');
    }
}
