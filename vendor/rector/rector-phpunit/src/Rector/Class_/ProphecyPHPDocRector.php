<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Core\NodeManipulator\ClassMethodPropertyFetchManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\ProphecyPHPDocRector\ProphecyPHPDocRectorTest
 */
final class ProphecyPHPDocRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodPropertyFetchManipulator
     */
    private $classMethodPropertyFetchManipulator;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->classMethodPropertyFetchManipulator = $classMethodPropertyFetchManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add correct @var to ObjectProphecy instances based on $this->prophesize() call.', [new CodeSample(<<<'CODE_SAMPLE'
class HelloTest extends TestCase
{
    /**
     * @var SomeClass
     */
    private $propesizedObject;

    public function setUp(): void
    {
        $this->propesizedObject = $this->prophesize(SomeClass::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class HelloTest extends TestCase
{
    /**
     * @var ObjectProphecy<SomeClass>
     */
    private $propesizedObject;

    public function setUp(): void
    {
        $this->propesizedObject = $this->prophesize(SomeClass::class);
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            $propertyName = $this->getName($property);
            $toPropertyAssignExprs = $this->findAssignToPropertyName($node, $propertyName);
            foreach ($toPropertyAssignExprs as $toPropertyAssignExpr) {
                $prophesizedObjectArg = $this->matchThisProphesizeMethodCallFirstArg($toPropertyAssignExpr);
                if (!$prophesizedObjectArg instanceof Arg) {
                    continue;
                }
                $prophesizedClass = $this->valueResolver->getValue($prophesizedObjectArg->value);
                if (!\is_string($prophesizedClass)) {
                    return null;
                }
                $this->changePropertyDoc($property, $prophesizedClass);
                $hasChanged = \true;
                break;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function matchThisProphesizeMethodCallFirstArg(Expr $expr) : ?Arg
    {
        if (!$expr instanceof MethodCall) {
            return null;
        }
        $var = $expr->var;
        if (!$var instanceof Variable) {
            return null;
        }
        if (!$this->isName($var, 'this')) {
            return null;
        }
        if (!$this->isName($expr->name, 'prophesize')) {
            return null;
        }
        return $expr->getArgs()[0];
    }
    /**
     * @return Expr[]
     */
    private function findAssignToPropertyName(Class_ $class, string $propertyName) : array
    {
        $assignExprs = [];
        foreach ($class->getMethods() as $classMethod) {
            $currentAssignExprs = $this->classMethodPropertyFetchManipulator->findAssignsToPropertyName($classMethod, $propertyName);
            $assignExprs = \array_merge($assignExprs, $currentAssignExprs);
        }
        return $assignExprs;
    }
    private function changePropertyDoc(Property $property, string $prophesizedClass) : void
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        // replace old one
        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
        $propertyPhpDocInfo->addTagValueNode(new VarTagValueNode(new GenericTypeNode(new FullyQualifiedIdentifierTypeNode('Prophecy\\Prophecy\\ObjectProphecy'), [new FullyQualifiedIdentifierTypeNode($prophesizedClass)]), '', ''));
    }
}
