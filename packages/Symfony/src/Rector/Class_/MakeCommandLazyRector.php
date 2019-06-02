<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://symfony.com/doc/current/console/commands_as_services.html
 */
final class MakeCommandLazyRector extends AbstractRector
{
    /**
     * @var string
     */
    private const COMMAND_CLASS = 'Symfony\Component\Console\Command\Command';

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make Symfony commands lazy', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

class SunshineCommand extends Command
{
    public function configure()
    {
        $this->setName('sunshine');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command

class SunshineCommand extends Command
{
    protected static $defaultName = 'sunshine';
    public function configure()
    {
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, self::COMMAND_CLASS)) {
            return null;
        }

        $commandName = $this->resolveCommandNameAndRemove($node);
        if ($commandName === null) {
            return null;
        }

        $defaultNameProperty = $this->createDefaultNameProperty($commandName);
        $node->stmts = array_merge([$defaultNameProperty], (array) $node->stmts);

        return $node;
    }

    private function createDefaultNameProperty(Node $commandNameNode): Node\Stmt\Property
    {
        return $this->builderFactory->property('defaultName')
            ->makeProtected()
            ->makeStatic()
            ->setDefault($commandNameNode)
            ->getNode();
    }

    private function resolveCommandNameAndRemove(Class_ $class): ?Node
    {
        $commandName = null;
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $class->stmts, function (Node $node) use (
            &$commandName
        ) {
            if ($node instanceof MethodCall) {
                if (! $this->isName($node, 'setName')) {
                    return null;
                }

                $commandName = $node->args[0]->value;
                $this->removeNode($node);
            }

            if ($node instanceof ClassMethod && $this->isName($node, '__construct')) {
                if (count((array) $node->stmts) !== 1) {
                    return null;
                }

                $onlyNode = $node->stmts[0];
                if ($onlyNode instanceof Expression) {
                    $onlyNode = $onlyNode->expr;
                    if (! $this->isName($onlyNode, '__construct')) {
                        return null;
                    }

                    $commandName = $onlyNode->args[0]->value;
                    if (! is_string($this->getValue($commandName))) {
                        return null;
                    }

                    if (count($node->params) === 0) {
                        $this->removeNode($node);
                    }
                }
            }

            if ($node instanceof StaticCall) {
                if (! $this->isName($node, '__construct')) {
                    return null;
                }

                $commandName = $node->args[0]->value;

                array_shift($node->args);
            }
        });

        return $commandName;
    }
}
