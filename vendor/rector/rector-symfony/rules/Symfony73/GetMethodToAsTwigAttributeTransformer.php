<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
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
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Symfony\Enum\TwigClass;
use Rector\Symfony\Symfony73\NodeAnalyzer\LocalArrayMethodCallableMatcher;
use Rector\Symfony\Symfony73\NodeRemover\ReturnEmptyArrayMethodRemover;
use Rector\Symfony\Symfony73\ValueObject\AsTwigAttributeConversion;
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
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @var mixed[]
     */
    private const OPTION_TO_NAMED_ARG = ['is_safe' => 'isSafe', 'needs_environment' => 'needsEnvironment', 'needs_context' => 'needsContext', 'needs_charset' => 'needsCharset', 'is_safe_callback' => 'isSafeCallback', 'deprecation_info' => 'deprecationInfo'];
    /**
     * Methods that keep a class registered as a classic Twig extension; while any of them is present,
     * "extends AbstractExtension" must stay and the class cannot be turned into an attribute-based one.
     *
     * @var string[]
     */
    private const TWIG_EXTENSION_METHODS = ['getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals'];
    public function __construct(LocalArrayMethodCallableMatcher $localArrayMethodCallableMatcher, ReturnEmptyArrayMethodRemover $returnEmptyArrayMethodRemover, ReflectionProvider $reflectionProvider, VisibilityManipulator $visibilityManipulator, NodeNameResolver $nodeNameResolver)
    {
        $this->localArrayMethodCallableMatcher = $localArrayMethodCallableMatcher;
        $this->returnEmptyArrayMethodRemover = $returnEmptyArrayMethodRemover;
        $this->reflectionProvider = $reflectionProvider;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
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
        $returnArray = $this->matchReturnArray($getMethod);
        if (!$returnArray instanceof Array_) {
            return \false;
        }
        // nothing to convert
        if ($returnArray->items === []) {
            return \false;
        }
        // validate every registration before changing anything: a partial conversion would
        // leave some filters/functions unregistered once "extends AbstractExtension" is removed
        $conversions = [];
        foreach ($returnArray->items as $key => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                return \false;
            }
            $conversion = $this->matchArrayItemConversion($key, $arrayItem, $class, $objectType, $additionalOptionMapping);
            if (!$conversion instanceof AsTwigAttributeConversion) {
                return \false;
            }
            $conversions[] = $conversion;
        }
        // attribute-based extensions and "extends AbstractExtension" are incompatible, so the class
        // must not keep relying on the parent class for other registrations (e.g. getTests(), globals)
        if ($this->stillRequiresAbstractExtension($class, $methodName)) {
            return \false;
        }
        foreach ($conversions as $conversion) {
            $nameArg = $conversion->getNameArg();
            $nameArg->name = new Identifier('name');
            $this->decorateMethodWithAttribute($conversion->getClassMethod(), $attributeClass, array_merge([$nameArg], $conversion->getOptionArgs()));
            $this->visibilityManipulator->makePublic($conversion->getClassMethod());
            // remove old new function/filter instance
            unset($returnArray->items[$conversion->getItemKey()]);
        }
        $this->returnEmptyArrayMethodRemover->removeClassMethodIfArrayEmpty($class, $returnArray, $methodName);
        if ($class->extends instanceof FullyQualified && $class->extends->toString() === TwigClass::TWIG_EXTENSION) {
            $class->extends = null;
        }
        return \true;
    }
    private function matchReturnArray(ClassMethod $classMethod): ?Array_
    {
        $returnArray = null;
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            // multiple/conditional returns cannot be converted safely
            if ($returnArray instanceof Array_) {
                return null;
            }
            if (!$stmt->expr instanceof Array_) {
                return null;
            }
            $returnArray = $stmt->expr;
        }
        return $returnArray;
    }
    /**
     * @param array<string, string> $additionalOptionMapping
     */
    private function matchArrayItemConversion(int $key, ArrayItem $arrayItem, Class_ $class, ObjectType $objectType, array $additionalOptionMapping): ?AsTwigAttributeConversion
    {
        if (!$arrayItem->value instanceof New_) {
            return null;
        }
        $new = $arrayItem->value;
        if ($new->isFirstClassCallable()) {
            return null;
        }
        $argCount = count($new->getArgs());
        if ($argCount > 3 || $argCount < 2) {
            return null;
        }
        $nameArg = $new->getArgs()[0];
        if (!$nameArg->value instanceof String_) {
            return null;
        }
        $secondArg = $new->getArgs()[1];
        $thirdArg = $new->getArgs()[2] ?? null;
        // the callable must be a local method; external services, other classes or null callables
        // cannot be expressed as an attribute on a method of this class
        if (!$this->isLocalCallable($secondArg->value)) {
            return null;
        }
        $localMethodName = $this->localArrayMethodCallableMatcher->match($secondArg->value, $objectType);
        if (!is_string($localMethodName)) {
            return null;
        }
        $localMethod = $class->getMethod($localMethodName);
        if (!$localMethod instanceof ClassMethod) {
            return null;
        }
        $optionArguments = $this->getArgumentsFromOptionArray($thirdArg, $additionalOptionMapping);
        if ($optionArguments === null) {
            return null;
        }
        return new AsTwigAttributeConversion($key, $localMethod, $nameArg, $optionArguments);
    }
    private function stillRequiresAbstractExtension(Class_ $class, string $convertedMethodName): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $currentMethodName = $classMethod->name->toString();
            if ($currentMethodName === $convertedMethodName) {
                continue;
            }
            if (in_array($currentMethodName, self::TWIG_EXTENSION_METHODS, \true)) {
                return \true;
            }
        }
        foreach ($class->implements as $implement) {
            if ($this->nodeNameResolver->isName($implement, TwigClass::GLOBALS_INTERFACE)) {
                return \true;
            }
            if ($this->nodeNameResolver->isName($implement, TwigClass::EXTENSION_INTERFACE)) {
                return \true;
            }
        }
        return \false;
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
