<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Enum\ClassName;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\ReturnTypeFromMockObjectRector\ReturnTypeFromMockObjectRectorTest
 */
final class ReturnTypeFromMockObjectRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnAnalyzer $returnAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add known property and return MockObject types', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest extends TestCase
{
    public function createSomeMock()
    {
        $someMock = $this->createMock(SomeClass::class);
        return $someMock;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest extends TestCase
{
    public function createSomeMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $someMock = $this->createMock(SomeClass::class);
        return $someMock;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classReflection = $this->fetchClassLikeReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        // is phpunit test case?
        if (!$classReflection->is(ClassName::TEST_CASE_CLASS)) {
            return null;
        }
        $hasChanged = \false;
        $scope = null;
        foreach ($node->getMethods() as $classMethod) {
            // type is already known
            if ($classMethod->returnType instanceof Node) {
                continue;
            }
            // we need exactly 1 return
            $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
            if (count($returns) !== 1) {
                continue;
            }
            if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($classMethod, $returns)) {
                continue;
            }
            $scope ??= ScopeFetcher::fetch($node);
            if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod, $scope)) {
                continue;
            }
            /** @var Expr $expr */
            $expr = $returns[0]->expr;
            $returnType = $this->nodeTypeResolver->getNativeType($expr);
            if (!$this->isMockObjectType($returnType)) {
                continue;
            }
            $classMethod->returnType = new FullyQualified(ClassName::MOCK_OBJECT);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_70;
    }
    private function isIntersectionWithMockObjectType(Type $type): bool
    {
        if (!$type instanceof IntersectionType) {
            return \false;
        }
        if (count($type->getTypes()) !== 2) {
            return \false;
        }
        return in_array(ClassName::MOCK_OBJECT, $type->getObjectClassNames());
    }
    private function isMockObjectType(Type $returnType): bool
    {
        if ($returnType instanceof ObjectType && $returnType->isInstanceOf(ClassName::MOCK_OBJECT)->yes()) {
            return \true;
        }
        return $this->isIntersectionWithMockObjectType($returnType);
    }
}
