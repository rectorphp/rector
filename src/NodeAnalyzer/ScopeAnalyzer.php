<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
final class ScopeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const NON_REFRESHABLE_NODES = [Name::class, Identifier::class, ComplexType::class];
    public function isRefreshable(Node $node): bool
    {
        $found = \true;
        foreach (self::NON_REFRESHABLE_NODES as $noScopeNode) {
            if (!!$node instanceof $noScopeNode) {
                $found = \false;
                break;
            }
        }
        return $found;
    }
}
