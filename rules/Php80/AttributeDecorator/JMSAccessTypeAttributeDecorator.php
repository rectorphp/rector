<?php

declare (strict_types=1);
namespace Rector\Php80\AttributeDecorator;

use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Php80\Contract\AttributeDecoratorInterface;
/**
 * The string is replaced by specific value
 */
final class JMSAccessTypeAttributeDecorator implements AttributeDecoratorInterface
{
    public function getAttributeName() : string
    {
        return 'JMS\\Serializer\\Annotation\\AccessType';
    }
    public function decorate(Attribute $attribute) : void
    {
        $args = $attribute->args;
        if (\count($args) !== 1) {
            return;
        }
        $currentArg = $args[0];
        if ($currentArg->name instanceof Identifier) {
            return;
        }
        if (!$currentArg->value instanceof String_) {
            return;
        }
        $currentArg->name = new Identifier('type');
    }
}
