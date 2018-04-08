<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Node\MethodCallNodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers https://github.com/nikic/PHP-Parser/commit/3da189769cfa19dabd890b85e1a4bfe63cfcc7fb
 */
final class UseWithAliasRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns use property to method and $node->alias to last name in UseAlias Node of PHP-Parser', [
            new CodeSample('$node->alias;', '$node->getAlias();'),
            new CodeSample('$node->name->getLast();', '$node->alias'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        return $this->propertyFetchAnalyzer->isTypeAndProperty($node, 'PhpParser\Node\Stmt\UseUse', 'alias');
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $getAliasMethodCall = $this->methodCallNodeFactory->createWithVariableAndMethodName(
            $propertyFetchNode->var,
            'getAlias'
        );

        return $this->methodCallNodeFactory->createWithVariableAndMethodName($getAliasMethodCall, 'toString');
    }
}
