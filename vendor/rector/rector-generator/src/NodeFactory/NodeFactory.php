<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\NodeFactory;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
final class NodeFactory
{
    /**
     * @param array<string|int, mixed> $values
     */
    public function createArray(array $values) : \PhpParser\Node\Expr\Array_
    {
        $arrayItems = [];
        foreach ($values as $key => $value) {
            // natural key, no need for value
            if (\is_int($key)) {
                $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem(\PhpParser\BuilderHelpers::normalizeValue($value));
            } else {
                $arrayItems[] = new \PhpParser\Node\Expr\ArrayItem(\PhpParser\BuilderHelpers::normalizeValue($value), \PhpParser\BuilderHelpers::normalizeValue($key));
            }
        }
        return new \PhpParser\Node\Expr\Array_($arrayItems);
    }
    public function createClassConstReference(string $class) : \PhpParser\Node\Expr\ClassConstFetch
    {
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($class);
        return new \PhpParser\Node\Expr\ClassConstFetch($fullyQualified, 'class');
    }
    public function createPropertyAssign(string $propertyName, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\Assign
    {
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
        return new \PhpParser\Node\Expr\Assign($propertyFetch, $expr);
    }
    public function createPublicMethod(string $methodName) : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder($methodName);
        $methodBuilder->makePublic();
        return $methodBuilder->getNode();
    }
    public function createPrivateArrayProperty(string $propertyName) : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220418\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder($propertyName);
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
