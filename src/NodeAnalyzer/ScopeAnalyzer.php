<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\MutatingScope;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class ScopeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const NO_SCOPE_NODES = [Name::class, Identifier::class, Param::class, Arg::class];
    /**
     * @var array<class-string<Stmt>>
     */
    private const RESOLVABLE_FROM_FILE_NODES = [Namespace_::class, FileWithoutNamespace::class];
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
        return \in_array(\get_class($node), self::RESOLVABLE_FROM_FILE_NODES, \true);
    }
}
