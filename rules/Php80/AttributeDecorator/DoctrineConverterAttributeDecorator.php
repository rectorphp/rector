<?php

declare (strict_types=1);
namespace Rector\Php80\AttributeDecorator;

use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
final class DoctrineConverterAttributeDecorator implements ConverterAttributeDecoratorInterface
{
    public function getAttributeName() : string
    {
        return 'Doctrine\\ORM\\Mapping\\Column';
    }
    public function decorate(Attribute $attribute) : void
    {
        foreach ($attribute->args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if ($arg->name->toString() !== 'nullable') {
                continue;
            }
            $value = $arg->value;
            if (!$value instanceof String_) {
                continue;
            }
            if (!\in_array($value->value, ['true', 'false'], \true)) {
                continue;
            }
            $arg->value = $value->value === 'true' ? new ConstFetch(new Name('true')) : new ConstFetch(new Name('false'));
            break;
        }
    }
}
