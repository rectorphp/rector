<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory;
final class ScopeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory
     */
    private $scopeFactory;
    /**
     * @var array<class-string<Node>>
     */
    private const REFRESHABLE_NODES = [Name::class, Identifier::class, Param::class, Arg::class, Variable::class];
    public function __construct(ScopeFactory $scopeFactory)
    {
        $this->scopeFactory = $scopeFactory;
    }
    public function isRefreshable(Node $node) : bool
    {
        foreach (self::REFRESHABLE_NODES as $noScopeNode) {
            if ($node instanceof $noScopeNode) {
                return \false;
            }
        }
        return \true;
    }
    public function resolveScope(Node $node, string $filePath) : ?Scope
    {
        // on File level
        if ($node instanceof Stmt && $node->getAttribute(AttributeKey::STATEMENT_DEPTH) === 0) {
            return $this->scopeFactory->createFromFile($filePath);
        }
        /**
         * Node and parent Node doesn't has Scope, and Node Start token pos is < 0,
         * it means the node and parent node just re-printed, the Scope need to be resolved from file
         */
        if ($node->getStartTokenPos() < 0) {
            return $this->scopeFactory->createFromFile($filePath);
        }
        return null;
    }
}
