<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
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
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Nette\DI\CompilerExtension') {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethod($node, 'setEntity')) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns $variable = new Nette\DI\Statement($oldArg, $variable->arguments);
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return new Assign(
            $methodCallNode->var,
            new New_(
                new Identifier('Nette\DI\Statement'),
                [
                    $methodCallNode->args[0],
                    new PropertyFetch($methodCallNode->var, 'arguments'),
                ]
            )
        );
    }
}
