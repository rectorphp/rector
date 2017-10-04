<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/269/files
 *
 * From:
 * $definition->setEntity('someEntity');
 *
 * To:
 * $definition = new Statement('someEntity', $definition->arguments);
 */
final class SetEntityToStatementRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        // ? is in class extending Nette\DI\CompilerExtension
        // ...

        // is Definition?
        // setEntity...
    }

    /**
     * @param Node\Expr\MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // metdhod call to variable assign
        $node->name->name = $this->newMethod;
        $node->args = $this->nodeFactory->createArgs($this->newArguments);

        return $node;
    }
}
