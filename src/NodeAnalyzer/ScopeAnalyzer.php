<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
final class ScopeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const NON_REFRESHABLE_NODES = [Name::class, Identifier::class, Arg::class, Variable::class, Attribute::class];
    public function isRefreshable(Node $node) : bool
    {
        foreach (self::NON_REFRESHABLE_NODES as $noScopeNode) {
            if ($node instanceof $noScopeNode) {
                return \false;
            }
        }
        return \true;
    }
}
