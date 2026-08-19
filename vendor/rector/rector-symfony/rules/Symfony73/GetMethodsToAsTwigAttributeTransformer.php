<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
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
use Rector\Symfony\Symfony73\ValueObject\GetMethodConversions;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-3-twig-extension-attributes
 */
final class GetMethodsToAsTwigAttributeTransformer
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
     * @var array<string, string>
     */
    private const METHOD_NAME_TO_ATTRIBUTE_CLASS = ['getFilters' => TwigClass::AS_TWIG_FILTER_ATTRIBUTE, 'getFunctions' => TwigClass::AS_TWIG_FUNCTION_ATTRIBUTE, 'getTests' => TwigClass::AS_TWIG_TEST_ATTRIBUTE];
    /**
     * Options shared by every Twig callable attribute
     *
     * @var array<string, string>
     */
    private const COMMON_OPTION_TO_NAMED_ARG = ['needs_environment' => 'needsEnvironment', 'needs_context' => 'needsContext', 'needs_charset' => 'needsCharset', 'deprecation_info' => 'deprecationInfo'];
    /**
     * Options exclusive to a single attribute, e.g. #[AsTwigTest] has no escaping options at all
     *
     * @var array<string, array<string, string>>
     */
    private const METHOD_NAME_TO_EXTRA_OPTION_TO_NAMED_ARG = ['getFilters' => ['is_safe' => 'isSafe', 'is_safe_callback' => 'isSafeCallback', 'preserves_safety' => 'preservesSafety', 'pre_escape' => 'preEscape'], 'getFunctions' => ['is_safe' => 'isSafe', 'is_safe_callback' => 'isSafeCallback'], 'getTests' => []];
    /**
     * Methods that keep a class registered as a classic Twig extension; while any of them is present,
     * "extends AbstractExtension" must stay and the class cannot be turned into an attribute-based one.
     *
     * @var string[]
     */
    private const TWIG_EXTENSION_METHODS = ['getTokenParsers', 'getNodeVisitors', 'getFilters', 'getTests', 'getFunctions', 'getOperators', 'getGlobals'];
    /**
     * Built-in Twig function names. Overriding one only works while the class stays a classic
     * "extends AbstractExtension", so such an item must remain in the array and not become an attribute.
     *
     * @var array<string, string[]>
     */
    private const METHOD_NAME_TO_CORE_TWIG_NAMES = ['getFunctions' => ['attribute', 'block', 'constant', 'cycle', 'date', 'dump', 'enum_cases', 'include', 'max', 'min', 'parent', 'random', 'range', 'source', 'template_from_string'], 'getFilters' => ['abs', 'batch', 'capitalize', 'column', 'convert_encoding', 'date', 'date_modify', 'default', 'e', 'escape', 'filter', 'first', 'format', 'join', 'json_encode', 'keys', 'last', 'length', 'lower', 'map', 'merge', 'nl2br', 'number_format', 'raw', 'reduce', 'replace', 'reverse', 'round', 'slice', 'sort', 'spaceless', 'split', 'striptags', 'title', 'trim', 'upper', 'url_encode'], 'getTests' => ['constant', 'defined', 'divisible by', 'empty', 'even', 'iterable', 'mapping', 'none', 'null', 'odd', 'same as', 'sequence', 'true']];
    public function __construct(LocalArrayMethodCallableMatcher $localArrayMethodCallableMatcher, ReturnEmptyArrayMethodRemover $returnEmptyArrayMethodRemover, ReflectionProvider $reflectionProvider, VisibilityManipulator $visibilityManipulator, NodeNameResolver $nodeNameResolver)
    {
        $this->localArrayMethodCallableMatcher = $localArrayMethodCallableMatcher;
        $this->returnEmptyArrayMethodRemover = $returnEmptyArrayMethodRemover;
        $this->reflectionProvider = $reflectionProvider;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function transformClassGetMethodsToAttributeMarkers(Class_ $class, ObjectType $objectType): bool
    {
        $getMethodConversions = [];
        foreach (self::METHOD_NAME_TO_ATTRIBUTE_CLASS as $methodName => $attributeClass) {
            $getMethod = $class->getMethod($methodName);
            if (!$getMethod instanceof ClassMethod) {
                continue;
            }
            $singleGetMethodConversions = $this->matchGetMethodConversions($class, $getMethod, $methodName, $attributeClass, $objectType);
            // the method is there, but cannot be converted; the class must keep relying on the parent
            // extension, so none of the other methods can be converted either
            if (!$singleGetMethodConversions instanceof GetMethodConversions) {
                return \false;
            }
            $getMethodConversions[] = $singleGetMethodConversions;
        }
        if ($getMethodConversions === []) {
            return \false;
        }
        $convertedMethodNames = array_map(static fn(GetMethodConversions $getMethodConversions): string => $getMethodConversions->getMethodName(), $getMethodConversions);
        // attribute-based extensions and "extends AbstractExtension" are incompatible, so the class
        // must not keep relying on the parent class for other registrations (e.g. token parsers, globals)
        if ($this->stillRequiresAbstractExtension($class, $convertedMethodNames)) {
            return \false;
        }
        foreach ($getMethodConversions as $getMethodConversion) {
            $this->applyGetMethodConversions($class, $getMethodConversion);
        }
        $this->removeAbstractExtensionIfEmptied($class, $getMethodConversions);
        return \true;
    }
    private function matchGetMethodConversions(Class_ $class, ClassMethod $classMethod, string $methodName, string $attributeClass, ObjectType $objectType): ?GetMethodConversions
    {
        // check if attribute even exists
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return null;
        }
        $returnArray = $this->matchReturnArray($classMethod);
        if (!$returnArray instanceof Array_) {
            return null;
        }
        // nothing to convert
        if ($returnArray->items === []) {
            return null;
        }
        // validate every registration before changing anything: a partial conversion would
        // leave some filters/functions/tests unregistered once "extends AbstractExtension" is removed
        $asTwigAttributeConversions = [];
        foreach ($returnArray->items as $key => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                return null;
            }
            // items that override a built-in Twig filter/function/test must stay registered the classic way,
            // so keep them in the array and let the class keep "extends AbstractExtension"
            if ($this->isBuiltinTwigNameOverride($arrayItem, $methodName)) {
                continue;
            }
            $asTwigAttributeConversion = $this->matchArrayItemConversion($key, $arrayItem, $class, $objectType, $methodName);
            if (!$asTwigAttributeConversion instanceof AsTwigAttributeConversion) {
                return null;
            }
            $asTwigAttributeConversions[] = $asTwigAttributeConversion;
        }
        // only built-in overrides (or none) left to convert, nothing to do
        if ($asTwigAttributeConversions === []) {
            return null;
        }
        return new GetMethodConversions($methodName, $attributeClass, $returnArray, $asTwigAttributeConversions);
    }
    private function applyGetMethodConversions(Class_ $class, GetMethodConversions $getMethodConversions): void
    {
        $returnArray = $getMethodConversions->getReturnArray();
        foreach ($getMethodConversions->getAsTwigAttributeConversions() as $asTwigAttributeConversion) {
            $nameArg = $asTwigAttributeConversion->getNameArg();
            $nameArg->name = new Identifier('name');
            $this->decorateMethodWithAttribute($asTwigAttributeConversion->getClassMethod(), $getMethodConversions->getAttributeClass(), array_merge([$nameArg], $asTwigAttributeConversion->getOptionArgs()));
            $this->visibilityManipulator->makePublic($asTwigAttributeConversion->getClassMethod());
            // remove old new filter/function/test instance
            unset($returnArray->items[$asTwigAttributeConversion->getItemKey()]);
        }
        $this->returnEmptyArrayMethodRemover->removeClassMethodIfArrayEmpty($class, $returnArray, $getMethodConversions->getMethodName());
    }
    /**
     * @param GetMethodConversions[] $getMethodConversions
     */
    private function removeAbstractExtensionIfEmptied(Class_ $class, array $getMethodConversions): void
    {
        // a kept built-in override leaves the array non-empty, so the class still needs the parent extension
        foreach ($getMethodConversions as $getMethodConversion) {
            if ($getMethodConversion->getReturnArray()->items !== []) {
                return;
            }
        }
        if (!$class->extends instanceof FullyQualified) {
            return;
        }
        if ($class->extends->toString() !== TwigClass::TWIG_EXTENSION) {
            return;
        }
        $class->extends = null;
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
    private function matchArrayItemConversion(int $key, ArrayItem $arrayItem, Class_ $class, ObjectType $objectType, string $methodName): ?AsTwigAttributeConversion
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
        $secondArgValue = $secondArg->value;
        if ($secondArgValue instanceof ArrowFunction && $secondArgValue->expr instanceof MethodCall && $secondArgValue->expr->name instanceof Identifier) {
            $localMethodName = $secondArgValue->expr->name->toString();
        } else {
            $localMethodName = $this->localArrayMethodCallableMatcher->match($secondArgValue, $objectType);
        }
        if (!\is_string($localMethodName)) {
            return null;
        }
        $localMethod = $class->getMethod($localMethodName);
        if (!$localMethod instanceof ClassMethod) {
            return null;
        }
        $optionArguments = $this->getArgumentsFromOptionArray($thirdArg, $methodName);
        if ($optionArguments === null) {
            return null;
        }
        return new AsTwigAttributeConversion($key, $localMethod, $nameArg, $optionArguments);
    }
    private function isBuiltinTwigNameOverride(ArrayItem $arrayItem, string $methodName): bool
    {
        if (!$arrayItem->value instanceof New_) {
            return \false;
        }
        $firstArg = $arrayItem->value->getArgs()[0] ?? null;
        if (!(($nullsafeVariable1 = $firstArg) ? $nullsafeVariable1->value : null) instanceof String_) {
            return \false;
        }
        return in_array($firstArg->value->value, self::METHOD_NAME_TO_CORE_TWIG_NAMES[$methodName], \true);
    }
    /**
     * @param string[] $convertedMethodNames
     */
    private function stillRequiresAbstractExtension(Class_ $class, array $convertedMethodNames): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $currentMethodName = $classMethod->name->toString();
            if (in_array($currentMethodName, $convertedMethodNames, \true)) {
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
        if ($expr instanceof Array_ && \count($expr->items) === 2) {
            return \true;
        }
        if ($expr instanceof ArrowFunction && $expr->expr instanceof MethodCall) {
            $methodCall = $expr->expr;
            if ($methodCall->var instanceof Variable && $methodCall->var->name === 'this') {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return Arg[]|null
     */
    private function getArgumentsFromOptionArray(?Arg $optionArgument, string $methodName): ?array
    {
        if (!(($nullsafeVariable2 = $optionArgument) ? $nullsafeVariable2->value : null) instanceof Array_) {
            return [];
        }
        $allOptionMappings = array_merge(self::COMMON_OPTION_TO_NAMED_ARG, self::METHOD_NAME_TO_EXTRA_OPTION_TO_NAMED_ARG[$methodName]);
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
