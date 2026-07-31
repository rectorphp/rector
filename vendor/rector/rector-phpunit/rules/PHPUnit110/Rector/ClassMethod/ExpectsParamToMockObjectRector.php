<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit110\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit110\Rector\ClassMethod\ExpectsParamToMockObjectRector\ExpectsParamToMockObjectRectorTest
 */
final class ExpectsParamToMockObjectRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=11.0');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change param type of a private test method to MockObject, when expects() is called on it', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private function prepareUserMock(SomeUser $user): void
    {
        $user->expects($this->once())
            ->method('getId');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private function prepareUserMock(MockObject $user): void
    {
        $user->expects($this->once())
            ->method('getId');
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
    public function refactor(Node $node): ?ClassMethod
    {
        if (!$node->isPrivate()) {
            return null;
        }
        if ($node->stmts === null || $node->params === []) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if (!$param->var instanceof Variable) {
                continue;
            }
            $paramName = $this->getName($param->var);
            if ($paramName === null) {
                continue;
            }
            if ($this->hasMockObjectType($param->type)) {
                continue;
            }
            // avoid contradicting the docblock type, that would have to be changed as well
            if ($phpDocInfo->getParamTagValueByName($paramName) instanceof ParamTagValueNode) {
                continue;
            }
            if (!$this->isExpectsCalledOnVariable($node, $paramName)) {
                continue;
            }
            $param->type = new FullyQualified(PHPUnitClassName::MOCK_OBJECT);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\ComplexType $type
     */
    private function hasMockObjectType($type): bool
    {
        if ($type instanceof Name) {
            return $this->isName($type, PHPUnitClassName::MOCK_OBJECT);
        }
        if ($type instanceof NullableType) {
            return $this->hasMockObjectType($type->type);
        }
        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->types as $singleType) {
                if ($this->hasMockObjectType($singleType)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function isExpectsCalledOnVariable(ClassMethod $classMethod, string $paramName): bool
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, MethodCall::class);
        foreach ($methodCalls as $methodCall) {
            if (!$methodCall->var instanceof Variable) {
                continue;
            }
            if (!$this->isName($methodCall->var, $paramName)) {
                continue;
            }
            if ($this->isName($methodCall->name, 'expects')) {
                return \true;
            }
        }
        return \false;
    }
}
