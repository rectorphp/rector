<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CakePHP\FullyQualifiedClassNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/AppUsesTask.php
 * @see https://github.com/cakephp/upgrade/search?q=uses&unscoped_q=uses
 *
 * @see \Rector\CakePHP\Tests\Rector\StaticCall\AppUsesStaticCallToUseStatementRector\AppUsesStaticCallToUseStatementRectorTest
 */
final class AppUsesStaticCallToUseStatementRector extends AbstractRector
{
    /**
     * @var FullyQualifiedClassNameResolver
     */
    private $fullyQualifiedClassNameResolver;

    public function __construct(FullyQualifiedClassNameResolver $fullyQualifiedClassNameResolver)
    {
        $this->fullyQualifiedClassNameResolver = $fullyQualifiedClassNameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change App::uses() to use imports', [
            new CodeSample(
                <<<'PHP'
App::uses('NotificationListener', 'Event');

CakeEventManager::instance()->attach(new NotificationListener());
PHP
,
                <<<'PHP'
use Event\NotificationListener;

CakeEventManager::instance()->attach(new NotificationListener());
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof StaticCall) {
            return null;
        }

        $staticCall = $node->expr;
        if (! $this->isAppUses($staticCall)) {
            return null;
        }

        $fullyQualifiedName = $this->createFullyQualifiedNameFromAppUsesStaticCall($staticCall);

        // A. is above the class or under the namespace
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Namespace_ || $parentNode === null) {
            return $this->createUseFromFullyQualifiedName($fullyQualifiedName);
        }

        // B. is inside the code → add use import
        $this->addUseType(new FullyQualifiedObjectType($fullyQualifiedName), $node);
        $this->removeNode($node);

        return null;
    }

    private function createFullyQualifiedNameFromAppUsesStaticCall(StaticCall $staticCall): string
    {
        /** @var string $shortClassName */
        $shortClassName = $this->getValue($staticCall->args[0]->value);

        /** @var string $namespaceName */
        $namespaceName = $this->getValue($staticCall->args[1]->value);

        return $this->fullyQualifiedClassNameResolver->resolveFromPseudoNamespaceAndShortClassName(
            $namespaceName,
            $shortClassName
        );
    }

    private function createUseFromFullyQualifiedName(string $fullyQualifiedName): Use_
    {
        $useUse = new UseUse(new Name($fullyQualifiedName));

        return new Use_([$useUse]);
    }

    private function isAppUses($staticCall): bool
    {
        if (! $this->isName($staticCall->class, 'App')) {
            return false;
        }

        return $this->isName($staticCall->name, 'uses');
    }
}
