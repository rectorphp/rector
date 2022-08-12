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
use Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
final class ScopeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const NO_SCOPE_NODES = [Name::class, Identifier::class, Param::class, Arg::class];
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory
     */
    private $scopeFactory;
    public function __construct(ScopeFactory $scopeFactory)
    {
        $this->scopeFactory = $scopeFactory;
    }
    public function hasScope(Node $node) : bool
    {
        foreach (self::NO_SCOPE_NODES as $noScopeNode) {
            if ($node instanceof $noScopeNode) {
                return \false;
            }
        }
        return \true;
    }
    public function resolveScope(Node $node, SmartFileInfo $smartFileInfo, ?MutatingScope $mutatingScope = null) : ?MutatingScope
    {
        if ($mutatingScope instanceof MutatingScope) {
            return $mutatingScope;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return $this->scopeFactory->createFromFile($smartFileInfo);
        }
        if (!$this->hasScope($parentNode)) {
            return $this->scopeFactory->createFromFile($smartFileInfo);
        }
        /** @var MutatingScope|null $parentScope */
        $parentScope = $parentNode->getAttribute(AttributeKey::SCOPE);
        return $parentScope;
    }
}
