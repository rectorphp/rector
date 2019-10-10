<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/33775/files
 * @see \Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\ConsoleExecuteReturnIntRectorTest
 */
final class ConsoleExecuteReturnIntRector extends AbstractRector
{
    private const COMMAND_CLASS = '\Symfony\Component\Console\Command\Command';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Returns int from Command::execute command', [
            new CodeSample(
                <<<'PHP'
class SomeCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return null;
    }
}
PHP
                ,
                <<<'PHP'
class SomeCommand extends Command
{
    public function index(InputInterface $input, OutputInterface $output): int
    {
        return 0;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->getName($node) !== 'execute') {
            return null;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class || ! $class instanceof Class_) {
            return null;
        }

        if (!$class->extends || $class->extends->toCodeString() !== self::COMMAND_CLASS) {
            return null;
        }

        $node->returnType = new Identifier('int');

        return $this->addReturn0ToMethod($node);
    }

    private function setReturnTo0InsteadOfNull(Return_ $return)
    {
        if (! $return->expr ) {
            $return->expr = new LNumber(0);
        }

        if ($return->expr instanceof ConstFetch && $this->getName($return->expr) === 'null') {
            $return->expr = new LNumber(0);
        }

        if ($return->expr instanceof MethodCall || $return->expr instanceof StaticCall) {
            // how to get the Node of teh called method?!
        }
    }

    private function addReturn0ToMethod(FunctionLike $node): FunctionLike
    {
        $hasReturn = false;
        $this->traverseNodesWithCallable($node->getStmts(), function (Node &$stmt) use ($node, &$hasReturn) {
            if (!$stmt instanceof Return_) {
                return;
            }

            if ($this->areNodesEqual($stmt->getAttribute(AttributeKey::PARENT_NODE), $node)) {
                $hasReturn = true;
            }

            $this->setReturnTo0InsteadOfNull($stmt);
        });

        if ($hasReturn) {
            return $node;
        }

        $node->stmts[] = new Return_(new LNumber(0));

        return $node;
    }
}
