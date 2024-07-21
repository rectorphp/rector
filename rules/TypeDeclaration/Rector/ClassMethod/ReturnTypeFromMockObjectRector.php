<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use RectorPrefix202407\PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromMockObjectRector\ReturnTypeFromMockObjectRectorTest
 */
final class ReturnTypeFromMockObjectRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var string
     */
    private const MOCK_OBJECT_CLASS = 'PHPUnit\\Framework\\MockObject\\MockObject';
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add known property and return MockObject types', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest extends TestCase
{
    public function test()
    {
        $someMock = $this->createMock(SomeClass::class);
        return $someMock;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest extends TestCase
{
    public function test(): \PHPUnit\Framework\MockObject\MockObject
    {
        $someMock = $this->createMock(SomeClass::class);
        return $someMock;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // type is already known
        if ($node->returnType instanceof Node) {
            return null;
        }
        if (!$this->isInsideTestCaseClass($node)) {
            return null;
        }
        // we need exactly 1 return
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (\count($returns) !== 1) {
            return null;
        }
        $soleReturn = $returns[0];
        if (!$soleReturn->expr instanceof Expr) {
            return null;
        }
        $returnType = $this->getType($soleReturn->expr);
        if (!$this->isMockObjectType($returnType)) {
            return null;
        }
        $node->returnType = new FullyQualified(self::MOCK_OBJECT_CLASS);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
    private function isIntersectionWithMockObjectType(Type $type) : bool
    {
        if (!$type instanceof IntersectionType) {
            return \false;
        }
        if (\count($type->getTypes()) !== 2) {
            return \false;
        }
        return \in_array(MockObject::class, $type->getObjectClassNames());
    }
    private function isMockObjectType(Type $returnType) : bool
    {
        if ($returnType instanceof ObjectType && $returnType->isInstanceOf(self::MOCK_OBJECT_CLASS)->yes()) {
            return \true;
        }
        return $this->isIntersectionWithMockObjectType($returnType);
    }
    private function isInsideTestCaseClass(ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // is phpunit test case?
        return $classReflection->isSubclassOf(TestCase::class);
    }
}
