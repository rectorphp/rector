<?php

declare (strict_types=1);
namespace Rector\Php72\NodeFactory;

use RectorPrefix202304\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Parser\InlineCodeParser;
use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php72\NodeManipulator\ClosureNestedUsesDecorator;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use ReflectionParameter;
final class AnonymousFunctionFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    private const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\\d+)#';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Rector\Php72\NodeManipulator\ClosureNestedUsesDecorator
     */
    private $closureNestedUsesDecorator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, NodeFactory $nodeFactory, StaticTypeMapper $staticTypeMapper, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, SimplePhpParser $simplePhpParser, ClosureNestedUsesDecorator $closureNestedUsesDecorator, AstResolver $astResolver, PrivatesAccessor $privatesAccessor, InlineCodeParser $inlineCodeParser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->simplePhpParser = $simplePhpParser;
        $this->closureNestedUsesDecorator = $closureNestedUsesDecorator;
        $this->astResolver = $astResolver;
        $this->privatesAccessor = $privatesAccessor;
        $this->inlineCodeParser = $inlineCodeParser;
    }
    /**
     * @api
     * @param Param[] $params
     * @param Stmt[] $stmts
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType|null $returnTypeNode
     */
    public function create(array $params, array $stmts, $returnTypeNode, bool $static = \false) : Closure
    {
        $useVariables = $this->createUseVariablesFromParams($stmts, $params);
        $anonymousFunctionNode = new Closure();
        $anonymousFunctionNode->params = $params;
        if ($static) {
            $anonymousFunctionNode->static = $static;
        }
        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode = $this->closureNestedUsesDecorator->applyNestedUses($anonymousFunctionNode, $useVariable);
            $anonymousFunctionNode->uses[] = new ClosureUse($useVariable);
        }
        if ($returnTypeNode instanceof Node) {
            $anonymousFunctionNode->returnType = $returnTypeNode;
        }
        $anonymousFunctionNode->stmts = $stmts;
        return $anonymousFunctionNode;
    }
    public function createFromPhpMethodReflection(PhpMethodReflection $phpMethodReflection, Expr $expr) : ?Closure
    {
        /** @var FunctionVariantWithPhpDocs $parametersAcceptorWithPhpDocs */
        $parametersAcceptorWithPhpDocs = ParametersAcceptorSelector::selectSingle($phpMethodReflection->getVariants());
        $newParams = $this->createParams($phpMethodReflection, $parametersAcceptorWithPhpDocs->getParameters());
        $innerMethodCall = $this->createInnerMethodCall($phpMethodReflection, $expr, $newParams);
        if ($innerMethodCall === null) {
            return null;
        }
        $returnTypeNode = null;
        if (!$parametersAcceptorWithPhpDocs->getReturnType() instanceof MixedType) {
            $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parametersAcceptorWithPhpDocs->getReturnType(), TypeKind::RETURN);
        }
        $uses = [];
        if ($expr instanceof Variable && !$this->nodeNameResolver->isName($expr, 'this')) {
            $uses[] = new ClosureUse($expr);
        }
        // does method return something?
        $stmts = $this->resolveStmts($parametersAcceptorWithPhpDocs, $innerMethodCall);
        return new Closure(['params' => $newParams, 'returnType' => $returnTypeNode, 'uses' => $uses, 'stmts' => $stmts]);
    }
    public function createAnonymousFunctionFromExpr(Expr $expr) : ?Closure
    {
        $stringValue = $this->inlineCodeParser->stringify($expr);
        $phpCode = '<?php ' . $stringValue . ';';
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        $anonymousFunction = new Closure();
        $firstNode = $contentStmts[0] ?? null;
        if (!$firstNode instanceof Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $node) : Node {
            if (!$node instanceof String_) {
                return $node;
            }
            $match = Strings::match($node->value, self::DIM_FETCH_REGEX);
            if ($match === null) {
                return $node;
            }
            $matchesVariable = new Variable('matches');
            return new ArrayDimFetch($matchesVariable, new LNumber((int) $match['number']));
        });
        $anonymousFunction->stmts[] = new Return_($stmt);
        $anonymousFunction->params[] = new Param(new Variable('matches'));
        $variables = $expr instanceof Variable ? [] : $this->betterNodeFinder->findInstanceOf($expr, Variable::class);
        $anonymousFunction->uses = \array_map(static function (Variable $variable) : ClosureUse {
            return new ClosureUse($variable);
        }, $variables);
        return $anonymousFunction;
    }
    /**
     * @param Param[] $params
     * @return string[]
     */
    private function collectParamNames(array $params) : array
    {
        $paramNames = [];
        foreach ($params as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }
        return $paramNames;
    }
    /**
     * @param Node[] $nodes
     * @param Param[] $params
     * @return array<string, Variable>
     */
    private function createUseVariablesFromParams(array $nodes, array $params) : array
    {
        $paramNames = $this->collectParamNames($params);
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf($nodes, Variable::class);
        /** @var array<string, Variable> $filteredVariables */
        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variables as $variable) {
            // "$this" is allowed
            if ($this->nodeNameResolver->isName($variable, 'this')) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($variable);
            if ($variableName === null) {
                continue;
            }
            if (\in_array($variableName, $paramNames, \true)) {
                continue;
            }
            $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Node && \in_array(\get_class($parentNode), [Assign::class, Foreach_::class, Param::class], \true)) {
                $alreadyAssignedVariables[] = $variableName;
            }
            if (!$this->nodeNameResolver->isNames($variable, $alreadyAssignedVariables)) {
                $filteredVariables[$variableName] = $variable;
            }
        }
        return $filteredVariables;
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     * @return Param[]
     */
    private function createParams(PhpMethodReflection $phpMethodReflection, array $parameterReflections) : array
    {
        $classReflection = $phpMethodReflection->getDeclaringClass();
        $className = $classReflection->getName();
        $methodName = $phpMethodReflection->getName();
        /** @var ClassMethod $classMethod */
        $classMethod = $this->astResolver->resolveClassMethod($className, $methodName);
        $params = [];
        foreach ($parameterReflections as $key => $parameterReflection) {
            $variable = new Variable($parameterReflection->getName());
            $defaultExpr = $this->resolveParamDefaultExpr($parameterReflection, $key, $classMethod);
            $type = $this->resolveParamType($parameterReflection);
            $byRef = $this->isParamByReference($parameterReflection);
            $params[] = new Param($variable, $defaultExpr, $type, $byRef);
        }
        return $params;
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|null
     */
    private function resolveParamType(ParameterReflection $parameterReflection)
    {
        if ($parameterReflection->getType() instanceof MixedType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType(), TypeKind::PARAM);
    }
    private function isParamByReference(ParameterReflection $parameterReflection) : bool
    {
        /** @var ReflectionParameter $reflection */
        $reflection = $this->privatesAccessor->getPrivateProperty($parameterReflection, 'reflection');
        return $reflection->isPassedByReference();
    }
    private function resolveParamDefaultExpr(ParameterReflection $parameterReflection, int $key, ClassMethod $classMethod) : ?Expr
    {
        if (!$parameterReflection->getDefaultValue() instanceof Type) {
            return null;
        }
        $paramDefaultExpr = $classMethod->params[$key]->default;
        if (!$paramDefaultExpr instanceof Expr) {
            return null;
        }
        // reset original node, to allow the printer to re-use the expr
        $paramDefaultExpr->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($paramDefaultExpr, static function (Node $node) : Node {
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            return $node;
        });
        return $paramDefaultExpr;
    }
    /**
     * @param Param[] $params
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function createInnerMethodCall(PhpMethodReflection $phpMethodReflection, Expr $expr, array $params)
    {
        if ($phpMethodReflection->isStatic()) {
            $expr = $this->normalizeClassConstFetchForStatic($expr);
            if ($expr === null) {
                return null;
            }
            $innerMethodCall = new StaticCall($expr, $phpMethodReflection->getName());
        } else {
            $expr = $this->resolveExpr($expr);
            if (!$expr instanceof Expr) {
                return null;
            }
            $innerMethodCall = new MethodCall($expr, $phpMethodReflection->getName());
        }
        $innerMethodCall->args = $this->nodeFactory->createArgsFromParams($params);
        return $innerMethodCall;
    }
    /**
     * @return null|\PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Expr
     */
    private function normalizeClassConstFetchForStatic(Expr $expr)
    {
        if (!$expr instanceof ClassConstFetch) {
            return $expr;
        }
        if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
            return $expr;
        }
        // dynamic name, nothing we can do
        $className = $this->nodeNameResolver->getName($expr->class);
        if ($className === null) {
            return null;
        }
        $name = new Name($className);
        if ($name->isSpecialClassName()) {
            return $name;
        }
        return new FullyQualified($className);
    }
    /**
     * @return \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr|null
     */
    private function resolveExpr(Expr $expr)
    {
        if (!$expr instanceof ClassConstFetch) {
            return $expr;
        }
        if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
            return $expr;
        }
        // dynamic name, nothing we can do
        $className = $this->nodeNameResolver->getName($expr->class);
        return $className === null ? null : new New_(new FullyQualified($className));
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $innerMethodCall
     */
    private function resolveStmts(FunctionVariantWithPhpDocs $functionVariantWithPhpDocs, $innerMethodCall) : array
    {
        if ($functionVariantWithPhpDocs->getReturnType()->isVoid()->yes()) {
            return [new Expression($innerMethodCall)];
        }
        return [new Return_($innerMethodCall)];
    }
}
