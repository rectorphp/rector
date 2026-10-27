<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-6-3-query-parameters-mapper
 * @changelog https://github.com/rectorphp/rector/issues/9937
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\RequestQueryToMapQueryParameterRector\RequestQueryToMapQueryParameterRectorTest
 */
final class RequestQueryToMapQueryParameterRector extends AbstractRector implements MinPhpVersionInterface, ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * Scalar getters of the query ParameterBag, mapped to the native type and the default value they return.
     *
     * @var array<string, string>
     */
    private const SCALAR_GETTER_TYPES = ['getInt' => 'int', 'getBoolean' => 'bool', 'getString' => 'string'];
    public function __construct(ControllerAnalyzer $controllerAnalyzer, BetterNodeFinder $betterNodeFinder, ValueResolver $valueResolver)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/http-kernel', '>=6.3');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move $request->query->getInt(), getBoolean(), getString() and getEnum() calls in controller actions to #[MapQueryParameter] action parameters', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class SomeController extends AbstractController
{
    public function list(Request $request)
    {
        $page = $request->query->getInt('page', 1);
        $isPublished = $request->query->getBoolean('published');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

final class SomeController extends AbstractController
{
    public function list(Request $request, #[MapQueryParameter] int $page = 1, #[MapQueryParameter(name: 'published')] bool $isPublished = false)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->isPublic() || $node->isAbstract() || $node->isStatic() || $node->stmts === null) {
            return null;
        }
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        $requestParamNames = $this->resolveRequestParamNames($node);
        if ($requestParamNames === []) {
            return null;
        }
        $paramNames = [];
        foreach ($node->params as $param) {
            if ($param->variadic) {
                return null;
            }
            $paramNames[] = $this->getName($param);
        }
        $newParams = [];
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable || !is_string($assign->var->name)) {
                continue;
            }
            $variableName = $assign->var->name;
            if (in_array($variableName, $paramNames, \true)) {
                continue;
            }
            if (!$assign->expr instanceof MethodCall || !$this->isRequestQueryCall($assign->expr, $requestParamNames)) {
                continue;
            }
            // the variable must not be used before it is assigned
            $previousStmts = array_slice($node->stmts, 0, (int) array_search($key, array_keys($node->stmts), \true));
            if ($this->betterNodeFinder->findVariableOfName($previousStmts, $variableName) instanceof Variable) {
                continue;
            }
            $param = $this->createParam($assign->expr, $variableName);
            if (!$param instanceof Param) {
                continue;
            }
            $newParams[] = $param;
            $paramNames[] = $variableName;
            unset($node->stmts[$key]);
        }
        if ($newParams === []) {
            return null;
        }
        $node->stmts = array_values($node->stmts);
        $node->params = array_merge($node->params, $newParams);
        // reprint the whole signature, so the new attributed parameters are printed consistently
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    /**
     * @return string[]
     */
    private function resolveRequestParamNames(ClassMethod $classMethod): array
    {
        $requestParamNames = [];
        foreach ($classMethod->params as $param) {
            if (!$param->type instanceof Node) {
                continue;
            }
            if (!$this->isObjectType($param->type, new ObjectType(SymfonyClass::REQUEST))) {
                continue;
            }
            $requestParamNames[] = $this->getName($param);
        }
        return $requestParamNames;
    }
    /**
     * @param string[] $requestParamNames
     */
    private function isRequestQueryCall(MethodCall $methodCall, array $requestParamNames): bool
    {
        if (!$methodCall->var instanceof PropertyFetch) {
            return \false;
        }
        $propertyFetch = $methodCall->var;
        if (!$this->isName($propertyFetch->name, 'query')) {
            return \false;
        }
        if (!$propertyFetch->var instanceof Variable) {
            return \false;
        }
        return $this->isNames($propertyFetch->var, $requestParamNames);
    }
    private function createParam(MethodCall $methodCall, string $variableName): ?Param
    {
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        $args = $methodCall->getArgs();
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier || $arg->unpack) {
                return null;
            }
        }
        if (!isset($args[0]) || !$args[0]->value instanceof String_) {
            return null;
        }
        $queryName = $args[0]->value->value;
        $methodName = $this->getName($methodCall->name);
        if (isset(self::SCALAR_GETTER_TYPES[$methodName])) {
            $type = new Identifier(self::SCALAR_GETTER_TYPES[$methodName]);
            $default = isset($args[1]) ? $args[1]->value : $this->createScalarDefault($methodName);
        } elseif ($methodName === 'getEnum') {
            if (!isset($args[1]) || !$this->isClassConstantReference($args[1]->value)) {
                return null;
            }
            /** @var ClassConstFetch $enumClassConstFetch */
            $enumClassConstFetch = $args[1]->value;
            /** @var Name $enumClass */
            $enumClass = $enumClassConstFetch->class;
            $default = isset($args[2]) ? $args[2]->value : new ConstFetch(new Name('null'));
            $enumType = new FullyQualified($enumClass->toString());
            $type = $this->valueResolver->isNull($default) ? new NullableType($enumType) : $enumType;
        } else {
            return null;
        }
        if (!$this->isConstantExpr($default)) {
            return null;
        }
        $attributeArgs = [];
        if ($queryName !== $variableName) {
            $attributeArgs[] = new Arg(new String_($queryName), \false, \false, [], new Identifier('name'));
        }
        $attributeGroup = new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::MAP_QUERY_PARAMETER), $attributeArgs)]);
        return new Param(new Variable($variableName), $default, $type, \false, \false, [], 0, [$attributeGroup]);
    }
    private function createScalarDefault(string $methodName): Expr
    {
        switch ($methodName) {
            case 'getInt':
                return new Int_(0);
            case 'getBoolean':
                return new ConstFetch(new Name('false'));
            default:
                return new String_('');
        }
    }
    private function isClassConstantReference(Expr $expr): bool
    {
        if (!$expr instanceof ClassConstFetch) {
            return \false;
        }
        if (!$expr->class instanceof Name) {
            return \false;
        }
        return $this->isName($expr->name, 'class');
    }
    /**
     * A parameter default value must be a constant expression
     */
    private function isConstantExpr(Expr $expr): bool
    {
        if ($expr instanceof String_ || $expr instanceof Int_ || $expr instanceof Float_) {
            return \true;
        }
        if ($expr instanceof ConstFetch) {
            return \true;
        }
        if ($expr instanceof ClassConstFetch) {
            return $expr->class instanceof Name && $expr->name instanceof Identifier;
        }
        if ($expr instanceof UnaryMinus) {
            return $expr->expr instanceof Int_ || $expr->expr instanceof Float_;
        }
        return \false;
    }
}
