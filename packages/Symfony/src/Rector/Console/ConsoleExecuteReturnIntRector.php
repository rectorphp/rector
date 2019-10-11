<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
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
    /**
     * @var string
     */
    private const COMMAND_CLASS = '\Symfony\Component\Console\Command\Command';

    /**
     * @var ClassLike[]
     */
    private $classes = [];

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

        if (! $class->extends || $class->extends->toCodeString() !== self::COMMAND_CLASS) {
            return null;
        }

        $node->returnType = new Identifier('int');

        return $this->addReturn0ToMethod($node);
    }

    public function beforeTraverse(array $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                $this->beforeTraverse($node->stmts);
            }

            if (! $node instanceof ClassLike) {
                continue;
            }

            $this->classes[$this->getName($node)] = $node;
        }
    }

    private function addReturn0ToMethod(FunctionLike $functionLike): FunctionLike
    {
        $hasReturn = false;
        $this->traverseNodesWithCallable($functionLike->getStmts(), function (Node &$stmt) use (
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
        }

        if ($return->expr instanceof ConstFetch && $this->getName($return->expr) === 'null') {
            $return->expr = new LNumber(0);
        }

        if ($return->expr instanceof Coalesce && $return->expr->right instanceof ConstFetch && $this->getName(
            $return->expr->right
        ) === 'null') {
            $return->expr->right = new LNumber(0);
        }

        if ($return->expr instanceof MethodCall) {
            $object = $this->getObjectType($return->expr->var);

            $this->refactorMethodOnObject($return, $object);
        }

        if ($return->expr instanceof StaticCall) {
            $object = $this->getObjectType($return->expr->class);

            $this->refactorMethodOnObject($return, $object);
        }
    }

    private function refactorMethodOnObject(Return_ $return, Type $type): void
    {
        if ($type instanceof ObjectType) {
            $this->refactorClassMethod($type->getClassName(), $this->getName($return->expr));
            return;
        }

        if ($type instanceof UnionType) {
            $baseClass = $type->getTypes()[0];
            if (! $baseClass instanceof ObjectType) {
                throw new ShouldNotHappenException();
            }

            $this->refactorClassMethod($baseClass->getClassName(), $this->getName($return->expr));
            return;
        }
    }

    private function refactorClassMethod(string $className, string $methodName): void
    {
        if (! array_key_exists($className, $this->classes)) {
            throw new ShouldNotHappenException();
        }

        $methods = $this->betterNodeFinder->find($this->classes[$className]->getMethods(), function (Node $node) use (
            $methodName
        ) {
            if (! $node instanceof ClassMethod) {
                return null;
            }

            if ($this->getName($node) === $methodName) {
                return $node;
            }

            return null;
        });

        if (count($methods) !== 1) {
            throw new ShouldNotHappenException();
        }

        $this->addReturn0ToMethod($methods[0]);
    }
}
