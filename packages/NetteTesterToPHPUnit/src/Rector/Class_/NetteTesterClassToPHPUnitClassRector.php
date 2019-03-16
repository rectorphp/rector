<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
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

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var string[]
     */
    private $assertMethodsRemap = [
        'same' => 'assertSame',
        'notSame' => 'assertNotSame',
        'equal' => 'assertEqual',
        'notEqual' => 'assertNotEqual',
        'true' => 'assertTrue',
        'false' => 'assertFalse',
        'null' => 'assertNull',
        'notNull' => 'assertNotNull',
        'count' => 'assertCount',
        'match' => 'assertStringMatchesFormat',
        'matchFile' => 'assertStringMatchesFormatFile',
        'nan' => 'assertIsNumeric',
    ];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(
        ClassManipulator $classManipulator,
        CallableNodeTraverser $callableNodeTraverser,
        DocBlockManipulator $docBlockManipulator,
        string $netteTesterTestCaseClass = 'Tester\TestCase'
    ) {
        $this->classManipulator = $classManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->netteTesterTestCaseClass = $netteTesterTestCaseClass;
        $this->docBlockManipulator = $docBlockManipulator;
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
    public function setUp()
    {
    }

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
    protected function setUp()
    {
    }

    public function testFunctionality()
    {
        self::assertInstanceOf(\Kdyby\Doctrine\EntityManager::cllass, $default);
        self::assertTrue(5);
        self::same($container->getService('kdyby.doctrine.default.entityManager'), $default);
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

        return;
    }

    private function processUnderTestRun(MethodCall $methodCall): void
    {
        if ($this->isName($methodCall, 'run')) {
            $this->removeNode($methodCall);
        }

        return;
    }

    private function processMethods(Class_ $class): void
    {
        $methods = $this->classManipulator->getMethods($class);
        foreach ($methods as $method) {
            if ($this->isNamesInsensitive($method, ['setUp', 'tearDown'])) {
                $this->makeProtected($method);
            }

            $this->processClassMethod($method);
        }
    }

    private function processAssertCalls(StaticCall $staticCall): void
    {
        $staticCall->class = new Name('self');

        if ($this->isNames($staticCall, ['contains', 'notContains'])) {
            $this->processContainsStaticCall($staticCall);
            return;
        }

        if ($this->isNames($staticCall, ['exception', 'throws'])) {
            $this->processExceptionStaticCall($staticCall);
            return;
        }

        if ($this->isName($staticCall, 'type')) {
            $this->processTypeStaticCall($staticCall);
            return;
        }

        if ($this->isName($staticCall, 'noError')) {
            $this->processNoErrorStaticCall($staticCall);
            return;
        }

        if ($this->isNames($staticCall, ['truthy', 'falsey'])) {
            $this->processTruthyOrFalseyStaticCall($staticCall);
            return;
        }

        foreach ($this->assertMethodsRemap as $oldMethod => $newMethod) {
            if ($this->isName($staticCall, $oldMethod)) {
                $staticCall->name = new Identifier($newMethod);
                continue;
            }
        }
    }

    private function processClassMethod(ClassMethod $classMethod): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof StaticCall) {
                return null;
            }

            if (! $this->isType($node->class, 'Tester\Assert')) {
                return null;
            }

            $this->processAssertCalls($node);
        });
    }

    private function processExceptionStaticCall(StaticCall $staticCall): void
    {
        // expect exception
        $expectException = new StaticCall(new Name('self'), 'expectException');
        $expectException->args[] = $staticCall->args[1];
        $this->addNodeAfterNode($expectException, $staticCall);

        // expect message
        if (isset($staticCall->args[2])) {
            $expectExceptionMessage = new StaticCall(new Name('self'), 'expectExceptionMessage');
            $expectExceptionMessage->args[] = $staticCall->args[2];
            $this->addNodeAfterNode($expectExceptionMessage, $staticCall);
        }

        // expect code
        if (isset($staticCall->args[3])) {
            $expectExceptionMessage = new StaticCall(new Name('self'), 'expectExceptionCode');
            $expectExceptionMessage->args[] = $staticCall->args[3];
            $this->addNodeAfterNode($expectExceptionMessage, $staticCall);
        }

        /** @var Closure $callable */
        $callable = $staticCall->args[0]->value;
        foreach ((array) $callable->stmts as $callableStmt) {
            $this->addNodeAfterNode($callableStmt, $staticCall);
        }

        $this->removeNode($staticCall);
    }

    private function processNoErrorStaticCall(StaticCall $staticCall): void
    {
        /** @var Closure $callable */
        $callable = $staticCall->args[0]->value;

        foreach ((array) $callable->stmts as $callableStmt) {
            $this->addNodeAfterNode($callableStmt, $staticCall);
        }

        $this->removeNode($staticCall);

        $methodNode = $staticCall->getAttribute(Attribute::METHOD_NODE);
        if ($methodNode === null) {
            return;
        }

        $phpDocTagNode = new PhpDocTextNode('@doesNotPerformAssertions');
        $this->docBlockManipulator->addTag($methodNode, $phpDocTagNode);
    }

    private function processTruthyOrFalseyStaticCall(StaticCall $staticCall): void
    {
        if (! $this->isBoolType($staticCall->args[0]->value)) {
            $staticCall->args[0]->value = new Bool_($staticCall->args[0]->value);
        }

        if ($this->isName($staticCall, 'truthy')) {
            $staticCall->name = new Identifier('assertTrue');
        } else {
            $staticCall->name = new Identifier('assertFalse');
        }
    }

    private function processTypeStaticCall(StaticCall $staticCall): void
    {
        $value = $this->getValue($staticCall->args[0]->value);

        $typeToMethod = [
            'list' => 'assertIsArray',
            'array' => 'assertIsArray',
            'bool' => 'assertIsBool',
            'callable' => 'assertIsCallable',
            'float' => 'assertIsFloat',
            'int' => 'assertIsInt',
            'integer' => 'assertIsInt',
            'object' => 'assertIsObject',
            'resource' => 'assertIsResource',
            'string' => 'assertIsString',
            'scalar' => 'assertIsScalar',
        ];

        if (isset($typeToMethod[$value])) {
            $staticCall->name = new Identifier($typeToMethod[$value]);
            unset($staticCall->args[0]);
            array_values($staticCall->args);
        } elseif ($value === 'null') {
            $staticCall->name = new Identifier('assertNull');
            unset($staticCall->args[0]);
            array_values($staticCall->args);
        } else {
            $staticCall->name = new Identifier('assertInstanceOf');
        }
    }

    private function processContainsStaticCall(StaticCall $staticCall): void
    {
        if ($this->isStringyType($staticCall->args[1]->value)) {
            $name = $this->isName(
                $staticCall,
                'contains'
            ) ? 'assertStringContainsString' : 'assertStringNotContainsString';
        } else {
            $name = $this->isName($staticCall, 'contains') ? 'assertContains' : 'assertNotContains';
        }

        $staticCall->name = new Identifier($name);
    }
}
