<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;

/**
 * @see https://symfony.com/doc/current/console/commands_as_services.html
 * @sponsor Thanks https://www.musement.com/ for sponsoring this rule; initiated by https://github.com/stloyd
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\MakeCommandLazyRectorTest
 */
final class MakeCommandLazyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make Symfony commands lazy', [
            new CodeSample(
                <<<'PHP'
use Symfony\Component\Console\Command\Command

class SunshineCommand extends Command
{
    public function configure()
    {
        $this->setName('sunshine');
    }
}
PHP
                ,
                <<<'PHP'
use Symfony\Component\Console\Command\Command

class SunshineCommand extends Command
{
    protected static $defaultName = 'sunshine';
    public function configure()
    {
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
        if (! $this->isObjectType($node, 'Symfony\Component\Console\Command\Command')) {
            return null;
        }

        $commandName = $this->resolveCommandNameAndRemove($node);
        if ($commandName === null) {
            return null;
        }

        $defaultNameProperty = $this->nodeFactory->createStaticProtectedPropertyWithDefault(
            'defaultName',
            $commandName
        );

        $node->stmts = array_merge([$defaultNameProperty], (array) $node->stmts);

        return $node;
    }

    private function resolveCommandNameAndRemove(Class_ $class): ?Node
    {
        $commandName = $this->resolveCommandNameFromConstructor($class);
        if ($commandName === null) {
            $commandName = $this->resolveCommandNameFromSetName($class);
        }

        $this->removeConstructorIfHasOnlySetNameMethodCall($class);

        return $commandName;
    }

    private function resolveCommandNameFromConstructor(Class_ $class): ?Node
    {
        $commandName = null;

        $this->traverseNodesWithCallable((array) $class->stmts, function (Node $node) use (&$commandName): ?void {
            if (! $node instanceof StaticCall) {
                return null;
            }
            if (! $this->isObjectType($node->class, 'Symfony\Component\Console\Command\Command')) {
                return null;
            }

            $commandName = $this->matchCommandNameNodeInConstruct($node);
            if ($commandName === null) {
                return null;
            }

            array_shift($node->args);
        });

        return $commandName;
    }

    private function resolveCommandNameFromSetName(Class_ $class): ?Node
    {
        $commandName = null;

        $this->traverseNodesWithCallable((array) $class->stmts, function (Node $node) use (&$commandName) {
            if (! $node instanceof MethodCall) {
                return null;
            }
            if (! $this->isObjectType($node->var, 'Symfony\Component\Console\Command\Command')) {
                return null;
            }

            if (! $this->isName($node->name, 'setName')) {
                return null;
            }

            $commandName = $node->args[0]->value;
            $commandNameStaticType = $this->getStaticType($commandName);
            if (! $commandNameStaticType instanceof StringType) {
                return null;
            }

            // is chain call? → remove by variable nulling
            if ($node->var instanceof MethodCall) {
                return $node->var;
            }

            $this->removeNode($node);
        });

        return $commandName;
    }

    private function removeConstructorIfHasOnlySetNameMethodCall(Class_ $class): void
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return;
        }

        if (count((array) $constructClassMethod->stmts) !== 1) {
            return;
        }

        $onlyNode = $constructClassMethod->stmts[0];
        if ($onlyNode instanceof Expression) {
            $onlyNode = $onlyNode->expr;
        }

        /** @var Expr|null $onlyNode */
        if ($onlyNode === null) {
            return;
        }

        if (! $onlyNode instanceof StaticCall) {
            return;
        }

        if ($onlyNode->args !== []) {
            return;
        }

        $this->removeNode($constructClassMethod);
    }

    private function matchCommandNameNodeInConstruct(Expr $expr): ?Node
    {
        if (! $expr instanceof MethodCall && ! $expr instanceof StaticCall) {
            return null;
        }

        if (! $this->isName($expr->name, MethodName::CONSTRUCT)) {
            return null;
        }

        if (count($expr->args) < 1) {
            return null;
        }

        $staticType = $this->getStaticType($expr->args[0]->value);
        if (! $staticType instanceof StringType) {
            return null;
        }

        return $expr->args[0]->value;
    }
}
