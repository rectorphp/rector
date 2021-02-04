<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://symfony.com/doc/current/console/commands_as_services.html
 * @sponsor Thanks https://www.musement.com/ for sponsoring this rule; initiated by https://github.com/stloyd
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector\MakeCommandLazyRectorTest
 */
final class MakeCommandLazyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make Symfony commands lazy', [
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
        if (! $this->isObjectType($node, 'Symfony\Component\Console\Command\Command')) {
            return null;
        }

        $commandName = $this->resolveCommandNameAndRemove($node);
        if (! $commandName instanceof Node) {
            return null;
        }

        $defaultNameProperty = $this->nodeFactory->createStaticProtectedPropertyWithDefault(
            'defaultName',
            $commandName
        );

        $node->stmts = array_merge([$defaultNameProperty], $node->stmts);

        return $node;
    }

    private function resolveCommandNameAndRemove(Class_ $class): ?Node
    {
        $commandName = $this->resolveCommandNameFromConstructor($class);
        if (! $commandName instanceof Node) {
            $commandName = $this->resolveCommandNameFromSetName($class);
        }

        $this->removeConstructorIfHasOnlySetNameMethodCall($class);

        return $commandName;
    }

    private function resolveCommandNameFromConstructor(Class_ $class): ?Node
    {
        $commandName = null;

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (&$commandName) {
            if (! $node instanceof StaticCall) {
                return null;
            }
            if (! $this->isObjectType($node->class, 'Symfony\Component\Console\Command\Command')) {
                return null;
            }

            $commandName = $this->matchCommandNameNodeInConstruct($node);
            if (! $commandName instanceof Expr) {
                return null;
            }

            array_shift($node->args);
        });

        return $commandName;
    }

    private function resolveCommandNameFromSetName(Class_ $class): ?Node
    {
        $commandName = null;

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (&$commandName) {
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
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        $stmts = (array) $constructClassMethod->stmts;
        if (count($stmts) !== 1) {
            return;
        }

        $params = $constructClassMethod->getParams();
        if ($params !== [] && $this->hasPropertyPromotion($params)) {
            return;
        }

        $onlyNode = $stmts[0];
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

        if (! $this->isName($onlyNode->name, MethodName::CONSTRUCT)) {
            return;
        }

        if ($onlyNode->args !== []) {
            return;
        }

        $this->removeNode($constructClassMethod);
    }

    /**
     * @param Param[] $params
     */
    private function hasPropertyPromotion(array $params): bool
    {
        foreach ($params as $param) {
            if ($param->flags !== 0) {
                return true;
            }
        }

        return false;
    }

    private function matchCommandNameNodeInConstruct(StaticCall $staticCall): ?Expr
    {
        if (! $this->isName($staticCall->name, MethodName::CONSTRUCT)) {
            return null;
        }

        if (count($staticCall->args) < 1) {
            return null;
        }

        $staticType = $this->getStaticType($staticCall->args[0]->value);
        if (! $staticType instanceof StringType) {
            return null;
        }

        return $staticCall->args[0]->value;
    }
}
