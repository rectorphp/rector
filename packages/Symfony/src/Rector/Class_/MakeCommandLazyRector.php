<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\StringType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\ValueObject\SymfonyClass;

/**
 * @see https://symfony.com/doc/current/console/commands_as_services.html
 * @sponsor Thanks https://www.musement.com/ for sponsoring this rule; initiated by https://github.com/stloyd
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
        if (! $this->isObjectType($node, SymfonyClass::COMMAND)) {
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

    private function createDefaultNameProperty(Node $commandNameNode): Property
    {
        $propertyBuilder = $this->builderFactory->property('defaultName');
        $propertyBuilder->makeProtected();
        $propertyBuilder->makeStatic();
        $propertyBuilder->setDefault($commandNameNode);

        return $propertyBuilder->getNode();
    }

    private function resolveCommandNameAndRemove(Class_ $class): ?Node
    {
        $commandName = null;

        $this->traverseNodesWithCallable((array) $class->stmts, function (Node $node) use (&$commandName) {
            if ($node instanceof MethodCall) {
                if (! $this->isObjectType($node->var, SymfonyClass::COMMAND)) {
                    return null;
                }

                if (! $this->isName($node, 'setName')) {
                    return null;
                }

                $commandName = $node->args[0]->value;
                $commandNameStaticType = $this->getStaticType($commandName);
                if (! $commandNameStaticType instanceof StringType) {
                    return null;
                }

                $this->removeNode($node);
            }

            if ($node instanceof StaticCall) {
                if (! $this->isObjectType($node->class, SymfonyClass::COMMAND)) {
                    return null;
                }

                $commandName = $this->matchCommandNameNodeInConstruct($node);
                if ($commandName === null) {
                    return null;
                }

                array_shift($node->args);
            }
        });

        $this->removeConstructorIfHasOnlySetNameMethodCall($class);

        return $commandName;
    }

    private function matchCommandNameNodeInConstruct(Expr $expr): ?Node
    {
        if (! $expr instanceof MethodCall && ! $expr instanceof StaticCall) {
            return null;
        }

        if (! $this->isName($expr, '__construct')) {
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

    private function removeConstructorIfHasOnlySetNameMethodCall(Class_ $class): void
    {
        $constructClassMethod = $class->getMethod('__construct');
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
}
