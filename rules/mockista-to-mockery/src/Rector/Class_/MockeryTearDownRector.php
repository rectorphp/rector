<?php

declare(strict_types=1);

namespace Rector\MockistaToMockery\Rector\Class_;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MockistaToMockery\MockistaDetector;

/**
 * @see \Rector\MockistaToMockery\Tests\Rector\Class_\MockeryTearDownRector\MockeryTearDownRectorTest
 */
final class MockeryTearDownRector extends AbstractRector
{
    /**
     * @var MockistaDetector
     */
    private $mockistaDetector;

    public function __construct(MockistaDetector $mockistaDetector)
    {
        $this->mockistaDetector = $mockistaDetector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add Mockery::close() in tearDown() method if not yet', [
            new CodeSample(
                <<<'PHP'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    public function test()
    {
        $mockUser = mock(User::class);
    }
}
PHP
,
                <<<'PHP'
use PHPUnit\Framework\TestCase;

class SomeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }
    public function test()
    {
        $mockUser = mock(User::class);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->mockistaDetector->isInClass($node)) {
            return null;
        }

        $tearDownClassMethod = $node->getMethod('tearDown');
        if ($tearDownClassMethod === null) {
            $node->stmts[] = $this->createTearDownMethodWithMockeryClose();
        } elseif (! $this->containsMockeryClose($tearDownClassMethod)) {
            $tearDownClassMethod->stmts[] = $this->createMockeryClose();
        }

        return $node;
    }

    private function createTearDownMethodWithMockeryClose(): ClassMethod
    {
        $methodBuilder = new MethodBuilder('tearDown');
        $methodBuilder->setReturnType('void');
        $methodBuilder->makeProtected();

        $staticCall = $this->createMockeryClose();
        $methodBuilder->addStmt($staticCall);

        return $methodBuilder->getNode();
    }

    private function containsMockeryClose(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node): bool {
            if (! $node instanceof StaticCall) {
                return false;
            }

            if (! $this->isName($node->class, 'Mockery')) {
                return false;
            }

            return $this->isName($node->name, 'close');
        });
    }

    private function createMockeryClose(): Stmt
    {
        $staticCall = $this->createStaticCall('Mockery', 'close');

        return BuilderHelpers::normalizeStmt($staticCall);
    }
}
