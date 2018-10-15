<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\MethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers https://github.com/nikic/PHP-Parser/commit/3da189769cfa19dabd890b85e1a4bfe63cfcc7fb
 */
final class UseWithAliasRector extends AbstractRector
{
    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns use property to method and `$node->alias` to last name in UseAlias Node of PHP-Parser',
            [
                new CodeSample('$node->alias;', '$node->getAlias();'),
                new CodeSample('$node->name->getLast();', '$node->alias'),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, 'PhpParser\Node\Stmt\UseUse')) {
            return null;
        }

        if (! $this->isName($node, 'alias')) {
            return null;
        }

        $getAliasMethodCall = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $node->var,
            'getAlias'
        );

        return $this->methodCallNodeFactory->createWithVariableAndMethodName($getAliasMethodCall, 'toString');
    }
}
