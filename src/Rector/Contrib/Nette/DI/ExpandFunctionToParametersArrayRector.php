<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\DI;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/Kdyby/Doctrine/pull/298/files
 * From:
 * $builder->expand('argument');
 * $builder->expand('%argument%');
 * To:
 * $builder->parameters['argument'];
 */
final class ExpandFunctionToParametersArrayRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, MethodArgumentAnalyzer $methodArgumentAnalyzer)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->methodArgumentAnalyzer = $methodArgumentAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Nette\DI\CompilerExtension') {
            return false;
        }

        if (! $this->methodCallAnalyzer->isMethodCallMethod($node, 'expand')
            || ! $this->methodArgumentAnalyzer->isMethodFirstArgumentString($node)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param MethodCall $methodCallNode
     *
     * Returns $builder->parameters['argument']
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $argument = $methodCallNode->args[0]->value;
        $argument->value = Strings::trim($argument->value, '%');

        return new ArrayDimFetch(
            new PropertyFetch(
                $methodCallNode->var,
                new Identifier('parameters')
            ),
            $argument
        );
    }
}
