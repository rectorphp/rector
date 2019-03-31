<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class NetteTesterClassToPHPUnitClassRector extends AbstractRector
{
    /**
     * @var string
     */
    private $netteTesterTestCaseClass;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(
        ClassManipulator $classManipulator,
        string $netteTesterTestCaseClass = 'Tester\TestCase'
    ) {
        $this->classManipulator = $classManipulator;
        $this->netteTesterTestCaseClass = $netteTesterTestCaseClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate Nette Tester test case to PHPUnit', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Include_::class, MethodCall::class];
    }

    /**
     * @param Class_|Include_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Include_) {
            $this->processAboveTestInclude($node);
            return null;
        }

        if (! $this->isType($node, $this->netteTesterTestCaseClass)) {
            return null;
        }

        if ($node instanceof MethodCall) {
            $this->processUnderTestRun($node);
            return null;
        }

        $this->processExtends($node);
        $this->processMethods($node);

        return $node;
    }

    private function processExtends(Class_ $class): void
    {
        $class->extends = new FullyQualified('PHPUnit\Framework\TestCase');
    }

    private function processAboveTestInclude(Include_ $include): void
    {
        if ($include->getAttribute(Attribute::CLASS_NODE) === null) {
            $this->removeNode($include);
        }
    }

    private function processUnderTestRun(MethodCall $methodCall): void
    {
        if ($this->isName($methodCall, 'run')) {
            $this->removeNode($methodCall);
        }
    }

    private function processMethods(Class_ $class): void
    {
        $methods = $this->classManipulator->getMethods($class);

        foreach ($methods as $method) {
            if ($this->isNamesInsensitive($method, ['setUp', 'tearDown'])) {
                $this->makeProtected($method);
            }
        }
    }
}
