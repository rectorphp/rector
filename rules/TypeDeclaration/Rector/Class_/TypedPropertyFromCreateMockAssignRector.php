<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeManipulator\ClassMethodPropertyFetchManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector\TypedPropertyFromCreateMockAssignRectorTest
 */
final class TypedPropertyFromCreateMockAssignRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassMethodPropertyFetchManipulator
     */
    private $classMethodPropertyFetchManipulator;
    /**
     * @var string
     */
    private const TEST_CASE_CLASS = 'PHPUnit\\Framework\\TestCase';
    /**
     * @var string
     */
    private const MOCK_OBJECT_CLASS = 'PHPUnit\\Framework\\MockObject\\MockObject';
    public function __construct(ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator)
    {
        $this->classMethodPropertyFetchManipulator = $classMethodPropertyFetchManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add typed property from assigned mock', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someProperty;

    protected function setUp(): void
    {
        $this->someProperty = $this->createMock(SomeMockedClass::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $someProperty;

    protected function setUp(): void
    {
        $this->someProperty = $this->createMock(SomeMockedClass::class);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType(self::TEST_CASE_CLASS))) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // already typed
            if ($property->type instanceof Node) {
                continue;
            }
            $propertyName = $this->getName($property);
            $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
            if (!$setUpClassMethod instanceof ClassMethod) {
                continue;
            }
            $assignedType = $this->resolveSingleAssignedExprType($setUpClassMethod, $propertyName);
            if (!$assignedType instanceof Type) {
                continue;
            }
            if (!$this->isMockObjectType($assignedType)) {
                continue;
            }
            $property->type = new FullyQualified(self::MOCK_OBJECT_CLASS);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isMockObjectType(Type $type) : bool
    {
        if ($type instanceof ObjectType && $type->isInstanceOf(self::MOCK_OBJECT_CLASS)->yes()) {
            return \true;
        }
        return $this->isIntersectionWithMockObjectType($type);
    }
    private function isIntersectionWithMockObjectType(Type $type) : bool
    {
        if (!$type instanceof IntersectionType) {
            return \false;
        }
        if (\count($type->getTypes()) !== 2) {
            return \false;
        }
        return \in_array(self::MOCK_OBJECT_CLASS, $type->getObjectClassNames());
    }
    private function resolveSingleAssignedExprType(ClassMethod $setUpClassMethod, string $propertyName) : ?Type
    {
        $assignedExprs = $this->classMethodPropertyFetchManipulator->findAssignsToPropertyName($setUpClassMethod, $propertyName);
        if (\count($assignedExprs) !== 1) {
            return null;
        }
        $assignedExpr = $assignedExprs[0];
        $exprType = $this->getType($assignedExpr);
        // work around finalized class mock
        if ($exprType instanceof NeverType && $assignedExpr instanceof MethodCall && $this->isName($assignedExpr->name, 'createMock')) {
            return new ObjectType(self::MOCK_OBJECT_CLASS);
        }
        return $exprType;
    }
}
