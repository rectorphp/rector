<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Ternary;
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
 * @see https://github.com/symfony/symfony/pull/33775/files
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
        if (! $this->isName($node, 'execute')) {
            return null;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $this->isObjectType($class, Command::class)) {
            return null;
        }

        $this->refactorReturnTypeDeclaration($node);
        $this->addReturn0ToMethod($node);

        return $node;
    }

    private function addReturn0ToMethod(ClassMethod $classMethod): void
    {
        $hasReturn = false;

        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use (
            $classMethod,
            &$hasReturn
        ): void {
            if (! $node instanceof Return_) {
                return;
            }

            // is there return without nesting?
            if ($this->areNodesEqual($node->getAttribute(AttributeKey::PARENT_NODE), $classMethod)) {
                $hasReturn = true;
            }

            $this->setReturnTo0InsteadOfNull($node);
        });

        if ($hasReturn) {
            return;
        }

        $classMethod->stmts[] = new Return_(new LNumber(0));
    }

    private function setReturnTo0InsteadOfNull(Return_ $return): void
    {
        if (! $return->expr) {
            $return->expr = new LNumber(0);
            return;
        }

        if ($this->isNull($return->expr)) {
            $return->expr = new LNumber(0);
            return;
        }

        if ($return->expr instanceof Coalesce && $this->isNull($return->expr->right)) {
            $return->expr->right = new LNumber(0);
            return;
        }

        if ($return->expr instanceof Ternary) {
            $hasChanged = $this->refactorTernaryReturn($return->expr);
            if ($hasChanged) {
                return;
            }
        }

        $staticType = $this->getStaticType($return->expr);
        if (! $staticType instanceof IntegerType) {
            $return->expr = new Int_($return->expr);
            return;
        }
    }

    private function refactorReturnTypeDeclaration(ClassMethod $classMethod): void
    {
        if ($classMethod->returnType) {
            // already set
            if ($this->isName($classMethod->returnType, 'int')) {
                return;
            }
        }

        $classMethod->returnType = new Identifier('int');
    }

    private function refactorTernaryReturn(Ternary $ternary): bool
    {
        $hasChanged = false;
        if ($ternary->if && $this->isNull($ternary->if)) {
            $ternary->if = new LNumber(0);
            $hasChanged = true;
        }

        if ($this->isNull($ternary->else)) {
            $ternary->else = new LNumber(0);
            $hasChanged = true;
        }

        return $hasChanged;
    }
}
