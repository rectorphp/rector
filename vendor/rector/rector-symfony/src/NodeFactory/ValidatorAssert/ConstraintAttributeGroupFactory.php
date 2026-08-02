<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\ValidatorAssert;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\PhpParser\Node\Value\ValueResolver;
final class ConstraintAttributeGroupFactory
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function create(string $constraintClass, New_ $constraintNew): AttributeGroup
    {
        $attribute = new Attribute(new FullyQualified($constraintClass), $this->createArgs($constraintNew));
        return new AttributeGroup([$attribute]);
    }
    /**
     * The options array is how constraints were configured before Symfony 7.3. In an attribute the named arguments
     * read better and are the only form left once the array option is removed.
     *
     * @return Arg[]
     */
    private function createArgs(New_ $constraintNew): array
    {
        $args = $constraintNew->getArgs();
        if (count($args) !== 1) {
            return $args;
        }
        $soleArgValue = $args[0]->value;
        if (!$soleArgValue instanceof Array_) {
            return $args;
        }
        // already a named argument, keep as it is
        if ($args[0]->name instanceof Identifier) {
            return $args;
        }
        $namedArgs = $this->createNamedArgs($soleArgValue);
        if ($namedArgs === null) {
            return $args;
        }
        return $namedArgs;
    }
    /**
     * @return Arg[]|null
     */
    private function createNamedArgs(Array_ $array): ?array
    {
        if ($array->items === []) {
            return null;
        }
        $namedArgs = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                return null;
            }
            if ($arrayItem->unpack) {
                return null;
            }
            // a list, e.g. new Assert\Callback(['validateCity']) - the array is the value itself
            if (!$arrayItem->key instanceof Expr) {
                return null;
            }
            $keyValue = $this->valueResolver->getValue($arrayItem->key);
            if (!is_string($keyValue)) {
                return null;
            }
            $arg = new Arg($arrayItem->value);
            $arg->name = new Identifier($keyValue);
            $namedArgs[] = $arg;
        }
        return $namedArgs;
    }
}
