<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Covers https://github.com/nikic/PHP-Parser/commit/3da189769cfa19dabd890b85e1a4bfe63cfcc7fb
 */
final class UseWithAliasRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->propertyFetchAnalyzer->isTypeAndProperty(
            $node,
            'PhpParser\Node\Stmt\UseUse',
            'alias'
        );
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $getAliasMethodCall = new MethodCall($propertyFetchNode->var, 'getAlias');

        return new MethodCall($getAliasMethodCall, 'toString');
    }
}
