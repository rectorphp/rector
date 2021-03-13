<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\FunctionLike;
use Rector\Core\PhpParser\Node\BetterNodeFinder;

final class SkipFinderCall
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return PropertyFetch[]
     */
    public function resolveAssignsToLocalPropertyFetches(FunctionLike $functionLike): array
    {
        return $this->betterNodeFinder->find((array) $functionLike->getStmts(), function (Node $node): bool {
            return true;
        });
    }
}
