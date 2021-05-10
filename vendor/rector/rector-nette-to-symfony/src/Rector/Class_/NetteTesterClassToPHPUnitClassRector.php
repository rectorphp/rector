<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\NetteTesterClassToPHPUnitClassRectorTest
 */
final class NetteTesterClassToPHPUnitClassRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate Nette Tester test case to PHPUnit', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
namespace KdybyTests\Doctrine;

use Tester\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class ExtensionTest extends TestCase
{
    public function testFunctionality()
    {
        Assert::true($default instanceof Kdyby\Doctrine\EntityManager);
        Assert::true(5);
        Assert::same($container->getService('kdyby.doctrine.default.entityManager'), $default);
    }
}

(new \ExtensionTest())->run();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace KdybyTests\Doctrine;

use Tester\TestCase;
use Tester\Assert;

class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testFunctionality()
    {
        $this->assertInstanceOf(\Kdyby\Doctrine\EntityManager::cllass, $default);
        $this->assertTrue(5);
        $this->same($container->getService('kdyby.doctrine.default.entityManager'), $default);
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
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Expr\Include_::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param Class_|Include_|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\Include_) {
            $this->processAboveTestInclude($node);
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            $this->processUnderTestRun($node);
            return null;
        }
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Tester\\TestCase'))) {
            return null;
        }
        $this->processExtends($node);
        $this->processMethods($node);
        return $node;
    }
    private function processAboveTestInclude(\PhpParser\Node\Expr\Include_ $include) : void
    {
        $classLike = $include->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            $this->removeNode($include);
        }
    }
    private function processUnderTestRun(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        if (!$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('Tester\\TestCase'))) {
            return;
        }
        if ($this->isName($methodCall->name, 'run')) {
            $this->removeNode($methodCall);
        }
    }
    private function processExtends(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $class->extends = new \PhpParser\Node\Name\FullyQualified('PHPUnit\\Framework\\TestCase');
    }
    private function processMethods(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($this->isNames($classMethod, [\Rector\Core\ValueObject\MethodName::SET_UP, \Rector\Core\ValueObject\MethodName::TEAR_DOWN])) {
                $this->visibilityManipulator->makeProtected($classMethod);
            }
        }
    }
}
