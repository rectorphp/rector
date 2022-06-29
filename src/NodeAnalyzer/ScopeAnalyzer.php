<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\Analyser\MutatingScope;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ScopeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const NO_SCOPE_NODES = [Name::class, Identifier::class, Param::class, Arg::class];
    public function hasScope(Node $node) : bool
    {
        foreach (self::NO_SCOPE_NODES as $noScopeNode) {
            if ($node instanceof $noScopeNode) {
                return \false;
            }
        }
        return \true;
    }
    public function isScopeResolvableFromFile(Node $node, ?MutatingScope $mutatingScope) : bool
    {
        if ($mutatingScope instanceof MutatingScope) {
            return \false;
        }
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        return !$parent instanceof Node;
    }
}
