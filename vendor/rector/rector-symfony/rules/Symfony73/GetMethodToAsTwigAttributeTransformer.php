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
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Symfony\Enum\TwigClass;
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
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    public function __construct(LocalArrayMethodCallableMatcher $localArrayMethodCallableMatcher, ReturnEmptyArrayMethodRemover $returnEmptyArrayMethodRemover, ReflectionProvider $reflectionProvider, VisibilityManipulator $visibilityManipulator)
    {
        $this->localArrayMethodCallableMatcher = $localArrayMethodCallableMatcher;
        $this->returnEmptyArrayMethodRemover = $returnEmptyArrayMethodRemover;
        $this->reflectionProvider = $reflectionProvider;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function transformClassGetMethodToAttributeMarker(Class_ $class, string $methodName, string $attributeClass, ObjectType $objectType): bool
    {
        // check if attribute even exists
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return \false;
        }
        $getMethod = $class->getMethod($methodName);
        if (!$getMethod instanceof ClassMethod) {
            return \false;
        }
        $originalMethod = clone $getMethod;
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
                if (count($new->getArgs()) !== 2) {
                    continue;
                }
                $nameArg = $new->getArgs()[0];
                if (!$nameArg->value instanceof String_) {
                    continue;
                }
                $secondArg = $new->getArgs()[1];
                if ($this->isLocalCallable($secondArg->value)) {
                    $localMethodName = $this->localArrayMethodCallableMatcher->match($secondArg->value, $objectType);
                    if (!is_string($localMethodName)) {
                        $getMethod = $originalMethod;
                        return \false;
                    }
                    $localMethod = $class->getMethod($localMethodName);
                    if (!$localMethod instanceof ClassMethod) {
                        continue;
                    }
                    $this->decorateMethodWithAttribute($localMethod, $attributeClass, $nameArg);
                    $this->visibilityManipulator->makePublic($localMethod);
                    // remove old new function instance
                    unset($returnArray->items[$key]);
                    $hasChanged = \true;
                }
            }
            $this->returnEmptyArrayMethodRemover->removeClassMethodIfArrayEmpty($class, $returnArray, $methodName);
        }
        if ($hasChanged && $class->extends instanceof FullyQualified && $class->extends->toString() === TwigClass::TWIG_EXTENSION) {
            $class->extends = null;
        }
        return $hasChanged;
    }
    private function decorateMethodWithAttribute(ClassMethod $classMethod, string $attributeClass, Arg $arg): void
    {
        $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified($attributeClass), [$arg])]);
    }
    private function isLocalCallable(Expr $expr): bool
    {
        if ($expr instanceof MethodCall && $expr->isFirstClassCallable()) {
            return \true;
        }
        return $expr instanceof Array_ && count($expr->items) === 2;
    }
}
