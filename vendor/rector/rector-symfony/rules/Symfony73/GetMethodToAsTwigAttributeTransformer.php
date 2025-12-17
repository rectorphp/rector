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
use PhpParser\Node\Identifier;
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
    private const OPTION_TO_NAMED_ARG = ['is_safe' => 'isSafe', 'needs_environment' => 'needsEnvironment', 'needs_context' => 'needsContext', 'needs_charset' => 'needsCharset', 'is_safe_callback' => 'isSafeCallback', 'deprecation_info' => 'deprecationInfo'];
    public function __construct(LocalArrayMethodCallableMatcher $localArrayMethodCallableMatcher, ReturnEmptyArrayMethodRemover $returnEmptyArrayMethodRemover, ReflectionProvider $reflectionProvider, VisibilityManipulator $visibilityManipulator)
    {
        $this->localArrayMethodCallableMatcher = $localArrayMethodCallableMatcher;
        $this->returnEmptyArrayMethodRemover = $returnEmptyArrayMethodRemover;
        $this->reflectionProvider = $reflectionProvider;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    /**
     * @param array<string, string> $additionalOptionMapping
     */
    public function transformClassGetMethodToAttributeMarker(Class_ $class, string $methodName, string $attributeClass, ObjectType $objectType, array $additionalOptionMapping = []): bool
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
                $argCount = count($new->getArgs());
                if ($argCount > 3 || $argCount < 2) {
                    continue;
                }
                $nameArg = $new->getArgs()[0];
                if (!$nameArg->value instanceof String_) {
                    continue;
                }
                $secondArg = $new->getArgs()[1];
                $thirdArg = $new->getArgs()[2] ?? null;
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
                    $optionArguments = $this->getArgumentsFromOptionArray($thirdArg, $additionalOptionMapping);
                    if ($optionArguments === null) {
                        continue;
                    }
                    $this->decorateMethodWithAttribute($localMethod, $attributeClass, array_merge([$nameArg], $optionArguments));
                    $this->visibilityManipulator->makePublic($localMethod);
                    // remove old new function instance
                    unset($returnArray->items[$key]);
                    $nameArg->name = new Identifier('name');
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
    /**
     * @param Arg[] $args
     */
    private function decorateMethodWithAttribute(ClassMethod $classMethod, string $attributeClass, array $args): void
    {
        $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified($attributeClass), $args)]);
    }
    private function isLocalCallable(Expr $expr): bool
    {
        if ($expr instanceof MethodCall && $expr->isFirstClassCallable()) {
            return \true;
        }
        return $expr instanceof Array_ && count($expr->items) === 2;
    }
    /**
     * @param array<string, string> $additionalOptionMapping
     *
     * @return Arg[]|null
     */
    private function getArgumentsFromOptionArray(?Arg $optionArgument, array $additionalOptionMapping): ?array
    {
        if (!(($nullsafeVariable1 = $optionArgument) ? $nullsafeVariable1->value : null) instanceof Array_) {
            return [];
        }
        $allOptionMappings = array_merge(self::OPTION_TO_NAMED_ARG, $additionalOptionMapping);
        $args = [];
        foreach ($optionArgument->value->items as $item) {
            if (!$item->key instanceof String_) {
                continue;
            }
            $mappedName = $allOptionMappings[$item->key->value] ?? null;
            if ($mappedName === null) {
                continue;
            }
            if ($mappedName === 'isSafeCallback' && ($item->value instanceof MethodCall && $item->value->isFirstClassCallable())) {
                continue;
            }
            $arg = new Arg($item->value);
            $arg->name = new Identifier($mappedName);
            $args[] = $arg;
        }
        $totalItems = count($optionArgument->value->items);
        return count($args) === $totalItems ? $args : null;
    }
}
