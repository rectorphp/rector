<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\IntegerType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\Console\Command\Command;

/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/33775/files
 * @see \Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector\ConsoleExecuteReturnIntRectorTest
 */
final class ConsoleExecuteReturnIntRector extends AbstractRector
{
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

        if (! $class->extends || $class->extends->toCodeString() !== '\\' . Command::class) {
            return null;
        }

        $node->returnType = new Identifier('int');

        return $this->addReturn0ToMethod($node);
    }

    private function addReturn0ToMethod(FunctionLike $functionLike): FunctionLike
    {
        $hasReturn = false;
        $this->traverseNodesWithCallable($functionLike->getStmts(), function (Node $stmt) use (
            $functionLike,
            &$hasReturn
        ): void {
            if (! $stmt instanceof Return_) {
                return;
            }

            if ($this->areNodesEqual($stmt->getAttribute(AttributeKey::PARENT_NODE), $functionLike)) {
                $hasReturn = true;
            }

            $this->setReturnTo0InsteadOfNull($stmt);
        });

        if ($hasReturn) {
            return $functionLike;
        }

        $functionLike->stmts[] = new Return_(new LNumber(0));

        return $functionLike;
    }

    private function setReturnTo0InsteadOfNull(Return_ $return): void
    {
        if (! $return->expr) {
            $return->expr = new LNumber(0);
            return;
        }

        if ($return->expr instanceof ConstFetch && $this->getName($return->expr) === 'null') {
            $return->expr = new LNumber(0);
            return;
        }

        if ($return->expr instanceof Coalesce && $return->expr->right instanceof ConstFetch && $this->getName(
            $return->expr->right
        ) === 'null') {
            $return->expr->right = new LNumber(0);
            return;
        }

        if (! $this->getStaticType($return->expr) instanceof IntegerType) {
            $return->expr = new Int_($return->expr);
            return;
        }
    }
}
