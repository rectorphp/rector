<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class ScopeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const NO_SCOPE_NODES = [\PhpParser\Node\Name::class, \PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class, \PhpParser\Node\Identifier::class, \PhpParser\Node\Stmt\Enum_::class, \PhpParser\Node\Param::class, \PhpParser\Node\Arg::class];
    public function hasScope(\PhpParser\Node $node) : bool
    {
        foreach (self::NO_SCOPE_NODES as $noScopeNode) {
            if ($node instanceof $noScopeNode) {
                return \false;
            }
        }
        return \true;
    }
}
