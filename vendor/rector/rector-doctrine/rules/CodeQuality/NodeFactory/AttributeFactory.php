<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\NodeFactory;

use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
final class AttributeFactory
{
    /**
     * @param mixed $expr
     */
    public static function createNamedArg($expr, string $name) : Arg
    {
        if (!$expr instanceof Expr) {
            $expr = BuilderHelpers::normalizeValue($expr);
        }
        return new Arg($expr, \false, \false, [], new Identifier($name));
    }
    /**
     * @param array<mixed|Arg> $values
     */
    public static function createGroup(string $className, array $values = []) : AttributeGroup
    {
        $builderFactory = new BuilderFactory();
        $args = $builderFactory->args($values);
        $attribute = new Attribute(new FullyQualified($className), $args);
        return new AttributeGroup([$attribute]);
    }
}
