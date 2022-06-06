<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\RectorGenerator\NodeFactory;

use RectorPrefix20220606\PhpParser\BuilderHelpers;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
final class NodeFactory
{
    /**
     * @param array<string|int, mixed> $values
     */
    public function createArray(array $values) : Array_
    {
        $arrayItems = [];
        foreach ($values as $key => $value) {
            // natural key, no need for value
            if (\is_int($key)) {
                $arrayItems[] = new ArrayItem(BuilderHelpers::normalizeValue($value));
            } else {
                $arrayItems[] = new ArrayItem(BuilderHelpers::normalizeValue($value), BuilderHelpers::normalizeValue($key));
            }
        }
        return new Array_($arrayItems);
    }
    public function createClassConstReference(string $class) : ClassConstFetch
    {
        $fullyQualified = new FullyQualified($class);
        return new ClassConstFetch($fullyQualified, 'class');
    }
    public function createPropertyAssign(string $propertyName, Expr $expr) : Assign
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
        return new Assign($propertyFetch, $expr);
    }
    public function createPublicMethod(string $methodName) : ClassMethod
    {
        $methodBuilder = new MethodBuilder($methodName);
        $methodBuilder->makePublic();
        return $methodBuilder->getNode();
    }
    public function createPrivateArrayProperty(string $propertyName) : Property
    {
        $propertyBuilder = new PropertyBuilder($propertyName);
        $propertyBuilder->makePrivate();
        $docContent = <<<'CODE_SAMPLE'
/**
 * @var mixed[]
 */
CODE_SAMPLE;
        $propertyBuilder->setDocComment($docContent);
        return $propertyBuilder->getNode();
    }
}
