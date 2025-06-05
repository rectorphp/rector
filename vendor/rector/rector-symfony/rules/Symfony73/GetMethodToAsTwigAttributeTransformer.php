<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Symfony\Symfony73\NodeAnalyzer\LocalArrayMethodCallableMatcher;
use Rector\Symfony\Symfony73\NodeRemover\ReturnEmptyArrayMethodRemover;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-3-twig-extension-attributes
 */
final class GetMethodToAsTwigAttributeTransformer
{
    /**
     * @readonly
     */
    private LocalArrayMethodCallableMatcher $localArrayMethodCallableMatcher;
    /**
     * @readonly
     */
    private ReturnEmptyArrayMethodRemover $returnEmptyArrayMethodRemover;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(LocalArrayMethodCallableMatcher $localArrayMethodCallableMatcher, ReturnEmptyArrayMethodRemover $returnEmptyArrayMethodRemover, ReflectionProvider $reflectionProvider)
    {
        $this->localArrayMethodCallableMatcher = $localArrayMethodCallableMatcher;
        $this->returnEmptyArrayMethodRemover = $returnEmptyArrayMethodRemover;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function transformClassGetMethodToAttributeMarker(Class_ $class, string $methodName, string $attributeClass, ObjectType $objectType) : bool
    {
        // check if attribute even exists
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return \false;
        }
        $getMethod = $class->getMethod($methodName);
        if (!$getMethod instanceof ClassMethod) {
            return \false;
        }
        $hasChanged = \false;
        foreach ((array) $getMethod->stmts as $stmt) {
            // handle return array simple case
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Array_) {
                continue;
            }
            $returnArray = $stmt->expr;
            foreach ($returnArray->items as $key => $arrayItem) {
                if (!$arrayItem->value instanceof New_) {
                    continue;
                }
                if ($arrayItem->value->isFirstClassCallable()) {
                    continue;
                }
                $new = $arrayItem->value;
                if (\count($new->getArgs()) !== 2) {
                    continue;
                }
                $nameArg = $new->getArgs()[0];
                if (!$nameArg->value instanceof String_) {
                    continue;
                }
                $secondArg = $new->getArgs()[1];
                if ($this->isLocalCallable($secondArg->value)) {
                    $localMethodName = $this->localArrayMethodCallableMatcher->match($secondArg->value, $objectType);
                    if (!\is_string($localMethodName)) {
                        continue;
                    }
                    $localMethod = $class->getMethod($localMethodName);
                    if (!$localMethod instanceof ClassMethod) {
                        continue;
                    }
                    $this->decorateMethodWithAttribute($localMethod, $attributeClass, $nameArg);
                    // remove old new fuction instance
                    unset($returnArray->items[$key]);
                    $hasChanged = \true;
                }
            }
            $this->returnEmptyArrayMethodRemover->removeClassMethodIfArrayEmpty($class, $returnArray, $methodName);
        }
        return $hasChanged;
    }
    private function decorateMethodWithAttribute(ClassMethod $classMethod, string $attributeClass, Arg $name) : void
    {
        $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified($attributeClass), [$name])]);
    }
    private function isLocalCallable(Expr $expr) : bool
    {
        if ($expr instanceof MethodCall && $expr->isFirstClassCallable()) {
            return \true;
        }
        return $expr instanceof Array_ && \count($expr->items) === 2;
    }
}
