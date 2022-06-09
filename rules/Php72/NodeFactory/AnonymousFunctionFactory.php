<?php

declare (strict_types=1);
namespace Rector\Php72\NodeFactory;

use RectorPrefix20220609\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\ConstFetch;
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
use PHPStan\Type\VoidType;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use ReflectionParameter;
use RectorPrefix20220609\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220609\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
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
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, NodeFactory $nodeFactory, StaticTypeMapper $staticTypeMapper, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, SimplePhpParser $simplePhpParser, NodeComparator $nodeComparator, AstResolver $astResolver, NodePrinterInterface $nodePrinter, PrivatesAccessor $privatesAccessor)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->simplePhpParser = $simplePhpParser;
        $this->nodeComparator = $nodeComparator;
        $this->astResolver = $astResolver;
        $this->nodePrinter = $nodePrinter;
        $this->privatesAccessor = $privatesAccessor;
    }
    /**
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
            $anonymousFunctionNode = $this->applyNestedUses($anonymousFunctionNode, $useVariable);
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
        /** @var FunctionVariantWithPhpDocs $functionVariantWithPhpDoc */
        $functionVariantWithPhpDoc = ParametersAcceptorSelector::selectSingle($phpMethodReflection->getVariants());
        $anonymousFunction = new Closure();
        $newParams = $this->createParams($phpMethodReflection, $functionVariantWithPhpDoc->getParameters());
        $anonymousFunction->params = $newParams;
        $innerMethodCall = $this->createInnerMethodCall($phpMethodReflection, $expr, $newParams);
        if ($innerMethodCall === null) {
            return null;
        }
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof MixedType) {
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($functionVariantWithPhpDoc->getReturnType(), TypeKind::RETURN);
            $anonymousFunction->returnType = $returnType;
        }
        // does method return something?
        $anonymousFunction->stmts[] = $functionVariantWithPhpDoc->getReturnType() instanceof VoidType ? new Expression($innerMethodCall) : new Return_($innerMethodCall);
        if ($expr instanceof Variable && !$this->nodeNameResolver->isName($expr, 'this')) {
            $anonymousFunction->uses[] = new ClosureUse($expr);
        }
        return $anonymousFunction;
    }
    public function createAnonymousFunctionFromString(Expr $expr) : ?Closure
    {
        if (!$expr instanceof String_) {
            // not supported yet
            throw new ShouldNotHappenException();
        }
        $phpCode = '<?php ' . $expr->value . ';';
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        $anonymousFunction = new Closure();
        $firstNode = $contentStmts[0] ?? null;
        if (!$firstNode instanceof Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (Node $node) : Node {
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
        return $anonymousFunction;
    }
    /**
     * @param ClosureUse[] $uses
     * @return ClosureUse[]
     */
    private function cleanClosureUses(array $uses) : array
    {
        $uniqueUses = [];
        foreach ($uses as $use) {
            if (!\is_string($use->var->name)) {
                continue;
            }
            $variableName = $use->var->name;
            if (\array_key_exists($variableName, $uniqueUses)) {
                continue;
            }
            $uniqueUses[$variableName] = $use;
        }
        return \array_values($uniqueUses);
    }
    private function applyNestedUses(Closure $anonymousFunctionNode, Variable $useVariable) : Closure
    {
        $parent = $this->betterNodeFinder->findParentType($useVariable, Closure::class);
        if (!$parent instanceof Closure) {
            return $anonymousFunctionNode;
        }
        $paramNames = $this->nodeNameResolver->getNames($parent->params);
        if ($this->nodeNameResolver->isNames($useVariable, $paramNames)) {
            return $anonymousFunctionNode;
        }
        $anonymousFunctionNode = clone $anonymousFunctionNode;
        while ($parent instanceof Closure) {
            $parentOfParent = $this->betterNodeFinder->findParentType($parent, Closure::class);
            $uses = [];
            while ($parentOfParent instanceof Closure) {
                $uses = $this->collectUsesEqual($parentOfParent, $uses, $useVariable);
                $parentOfParent = $this->betterNodeFinder->findParentType($parentOfParent, Closure::class);
            }
            $uses = \array_merge($parent->uses, $uses);
            $uses = $this->cleanClosureUses($uses);
            $parent->uses = $uses;
            $parent = $this->betterNodeFinder->findParentType($parent, Closure::class);
        }
        return $anonymousFunctionNode;
    }
    /**
     * @param ClosureUse[] $uses
     * @return ClosureUse[]
     */
    private function collectUsesEqual(Closure $closure, array $uses, Variable $useVariable) : array
    {
        foreach ($closure->params as $param) {
            if ($this->nodeComparator->areNodesEqual($param->var, $useVariable)) {
                $uses[] = new ClosureUse($param->var);
            }
        }
        return $uses;
    }
    /**
     * @param Param[] $paramNodes
     * @return string[]
     */
    private function collectParamNames(array $paramNodes) : array
    {
        $paramNames = [];
        foreach ($paramNodes as $paramNode) {
            $paramNames[] = $this->nodeNameResolver->getName($paramNode);
        }
        return $paramNames;
    }
    /**
     * @param Node[] $nodes
     * @param Param[] $paramNodes
     * @return array<string, Variable>
     */
    private function createUseVariablesFromParams(array $nodes, array $paramNodes) : array
    {
        $paramNames = $this->collectParamNames($paramNodes);
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
            $param = new Param(new Variable($parameterReflection->getName()));
            $this->applyParamType($param, $parameterReflection);
            $this->applyParamDefaultValue($param, $parameterReflection, $key, $classMethod);
            $this->applyParamByReference($param, $parameterReflection);
            $params[] = $param;
        }
        return $params;
    }
    private function applyParamType(Param $param, ParameterReflection $parameterReflection) : void
    {
        if ($parameterReflection->getType() instanceof MixedType) {
            return;
        }
        $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType(), TypeKind::PARAM);
    }
    private function applyParamByReference(Param $param, ParameterReflection $parameterReflection) : void
    {
        /** @var ReflectionParameter $reflection */
        $reflection = $this->privatesAccessor->getPrivateProperty($parameterReflection, 'reflection');
        $param->byRef = $reflection->isPassedByReference();
    }
    private function applyParamDefaultValue(Param $param, ParameterReflection $parameterReflection, int $key, ClassMethod $classMethod) : void
    {
        if (!$parameterReflection->getDefaultValue() instanceof Type) {
            return;
        }
        $printDefaultValue = $this->nodePrinter->print($classMethod->params[$key]->default);
        $param->default = new ConstFetch(new Name($printDefaultValue));
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
        return $name->isSpecialClassName() ? $name : new FullyQualified($className);
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
}
