<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Rector\Assign;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use RectorPrefix20220606\Rector\Naming\Guard\BreakingVariableRenameGuard;
use RectorPrefix20220606\Rector\Naming\Matcher\VariableAndCallAssignMatcher;
use RectorPrefix20220606\Rector\Naming\Naming\ExpectedNameResolver;
use RectorPrefix20220606\Rector\Naming\NamingConvention\NamingConventionAnalyzer;
use RectorPrefix20220606\Rector\Naming\PhpDoc\VarTagValueNodeRenamer;
use RectorPrefix20220606\Rector\Naming\ValueObject\VariableAndCallAssign;
use RectorPrefix20220606\Rector\Naming\VariableRenamer;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\RenameVariableToMatchMethodCallReturnTypeRectorTest
 */
final class RenameVariableToMatchMethodCallReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Guard\BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ExpectedNameResolver
     */
    private $expectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\NamingConvention\NamingConventionAnalyzer
     */
    private $namingConventionAnalyzer;
    /**
     * @readonly
     * @var \Rector\Naming\PhpDoc\VarTagValueNodeRenamer
     */
    private $varTagValueNodeRenamer;
    /**
     * @readonly
     * @var \Rector\Naming\Matcher\VariableAndCallAssignMatcher
     */
    private $variableAndCallAssignMatcher;
    /**
     * @readonly
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(BreakingVariableRenameGuard $breakingVariableRenameGuard, ExpectedNameResolver $expectedNameResolver, NamingConventionAnalyzer $namingConventionAnalyzer, VarTagValueNodeRenamer $varTagValueNodeRenamer, VariableAndCallAssignMatcher $variableAndCallAssignMatcher, VariableRenamer $variableRenamer, TypeUnwrapper $typeUnwrapper, ReflectionProvider $reflectionProvider, FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->namingConventionAnalyzer = $namingConventionAnalyzer;
        $this->varTagValueNodeRenamer = $varTagValueNodeRenamer;
        $this->variableAndCallAssignMatcher = $variableAndCallAssignMatcher;
        $this->variableRenamer = $variableRenamer;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename variable to match method return type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
public function run()
{
    $a = $this->getRunner();
}

public function getRunner(): Runner
{
    return new Runner();
}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
public function run()
{
    $runner = $this->getRunner();
}

public function getRunner(): Runner
{
    return new Runner();
}
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        $variableAndCallAssign = $this->variableAndCallAssignMatcher->match($node);
        if (!$variableAndCallAssign instanceof VariableAndCallAssign) {
            return null;
        }
        $call = $variableAndCallAssign->getCall();
        if ($this->isMultipleCall($call)) {
            return null;
        }
        $expectedName = $this->expectedNameResolver->resolveForCall($call);
        if ($expectedName === null) {
            return null;
        }
        if ($this->isName($node->var, $expectedName)) {
            return null;
        }
        if ($this->shouldSkip($variableAndCallAssign, $expectedName)) {
            return null;
        }
        $this->renameVariable($variableAndCallAssign, $expectedName);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $callNode
     */
    private function isMultipleCall($callNode) : bool
    {
        $parentNode = $callNode->getAttribute(AttributeKey::PARENT_NODE);
        $callNodeClass = \get_class($callNode);
        while ($parentNode instanceof Node) {
            $usedNodes = $this->betterNodeFinder->find($parentNode, function (Node $node) use($callNodeClass, $callNode) : bool {
                $nodeClass = \get_class($node);
                if ($callNodeClass !== $nodeClass) {
                    return \false;
                }
                $usedNodeOriginalNode = $callNode->getAttribute(AttributeKey::ORIGINAL_NODE);
                if (!$usedNodeOriginalNode instanceof Node) {
                    return \false;
                }
                if (\get_class($usedNodeOriginalNode) !== \get_class($callNode)) {
                    return \false;
                }
                /** @var FuncCall|StaticCall|MethodCall $node */
                $passedNode = clone $node;
                /** @var FuncCall|StaticCall|MethodCall $usedNodeOriginalNode */
                $usedNode = clone $usedNodeOriginalNode;
                /** @var FuncCall|StaticCall|MethodCall $passedNode */
                $passedNode->args = [];
                /** @var FuncCall|StaticCall|MethodCall $usedNode */
                $usedNode->args = [];
                return $this->nodeComparator->areNodesEqual($passedNode, $usedNode);
            });
            if (\count($usedNodes) > 1) {
                return \true;
            }
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }
        return \false;
    }
    private function shouldSkip(VariableAndCallAssign $variableAndCallAssign, string $expectedName) : bool
    {
        if ($this->namingConventionAnalyzer->isCallMatchingVariableName($variableAndCallAssign->getCall(), $variableAndCallAssign->getVariableName(), $expectedName)) {
            return \true;
        }
        if ($this->isClassTypeWithChildren($variableAndCallAssign->getCall())) {
            return \true;
        }
        return $this->breakingVariableRenameGuard->shouldSkipVariable($variableAndCallAssign->getVariableName(), $expectedName, $variableAndCallAssign->getFunctionLike(), $variableAndCallAssign->getVariable());
    }
    private function renameVariable(VariableAndCallAssign $variableAndCallAssign, string $expectedName) : void
    {
        $assign = $variableAndCallAssign->getAssign();
        $assignPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($assign);
        $this->varTagValueNodeRenamer->renameAssignVarTagVariableName($assignPhpDocInfo, $variableAndCallAssign->getVariableName(), $expectedName);
        $this->variableRenamer->renameVariableInFunctionLike($variableAndCallAssign->getFunctionLike(), $variableAndCallAssign->getVariableName(), $expectedName, $variableAndCallAssign->getAssign());
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    private function isClassTypeWithChildren($expr) : bool
    {
        $callStaticType = $this->getType($expr);
        $callStaticType = $this->typeUnwrapper->unwrapNullableType($callStaticType);
        if (!$callStaticType instanceof ObjectType) {
            return \false;
        }
        if (\is_a($callStaticType->getClassName(), ClassLike::class, \true)) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($callStaticType->getClassName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($callStaticType->getClassName());
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        return $childrenClassReflections !== [];
    }
}
