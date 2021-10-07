<?php

declare (strict_types=1);
namespace Rector\MockeryToProphecy\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\MockeryToProphecy\Collector\MockVariableCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\MockeryToProphecy\Rector\ClassMethod\MockeryToProphecyRector\MockeryToProphecyRectorTest
 */
final class MockeryCreateMockToProphizeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, class-string>
     */
    private $mockVariableTypesByNames = [];
    /**
     * @var \Rector\MockeryToProphecy\Collector\MockVariableCollector
     */
    private $mockVariableCollector;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\MockeryToProphecy\Collector\MockVariableCollector $mockVariableCollector, \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->mockVariableCollector = $mockVariableCollector;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $this->replaceMockCreationsAndCollectVariableNames($node);
        $this->revealMockArguments($node);
        return $node;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes mockery mock creation to Prophesize', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$mock = \Mockery::mock('MyClass');
$service = new Service();
$service->injectDependency($mock);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
 $mock = $this->prophesize('MyClass');

$service = new Service();
$service->injectDependency($mock->reveal());
CODE_SAMPLE
)]);
    }
    private function replaceMockCreationsAndCollectVariableNames(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        $this->traverseNodesWithCallable($classMethod->stmts, function (\PhpParser\Node $node) : ?MethodCall {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return null;
            }
            $callerType = $this->nodeTypeResolver->getType($node->class);
            if (!$callerType->isSuperTypeOf(new \PHPStan\Type\ObjectType('Mockery'))->yes()) {
                return null;
            }
            if (!$this->isName($node->name, 'mock')) {
                return null;
            }
            $collectedVariableTypesByNames = $this->mockVariableCollector->collectMockVariableName($node);
            $this->mockVariableTypesByNames = \array_merge($this->mockVariableTypesByNames, $collectedVariableTypesByNames);
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentNode instanceof \PhpParser\Node\Arg) {
                $prophesizeMethodCall = $this->createProphesizeMethodCall($node);
                return $this->nodeFactory->createMethodCall($prophesizeMethodCall, 'reveal');
            }
            return $this->createProphesizeMethodCall($node);
        });
    }
    private function revealMockArguments(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        $this->traverseNodesWithCallable($classMethod->stmts, function (\PhpParser\Node $node) : ?MethodCall {
            if (!$node instanceof \PhpParser\Node\Arg) {
                return null;
            }
            if (!$node->value instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            /** @var string $variableName */
            $variableName = $this->getName($node->value);
            if (!isset($this->mockVariableTypesByNames[$variableName])) {
                return null;
            }
            return $this->nodeFactory->createMethodCall($node->value, 'reveal');
        });
    }
    private function createProphesizeMethodCall(\PhpParser\Node\Expr\StaticCall $staticCall) : \PhpParser\Node\Expr\MethodCall
    {
        return $this->nodeFactory->createLocalMethodCall('prophesize', [$staticCall->args[0]]);
    }
}
