<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/269/files
 * From:
 * $definition->setEntity('someEntity');
 * To:
 * $definition = new Statement('someEntity', $definition->arguments);
 */
final class ExpandFunctionToParametersArrayRector extends AbstractRector
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

        if (!$this->methodCallAnalyzer->isMethodCallMethod($node, 'expand')) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns $builder->parameters['appDir']
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        return new ArrayDimFetch(
            new PropertyFetch(
                $methodCallNode->var,
                new Identifier('parameters')
            ),
            new String_('appDir')
        );
    }
}
