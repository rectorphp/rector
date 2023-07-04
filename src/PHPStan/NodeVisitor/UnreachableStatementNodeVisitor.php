<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\MutatingScope;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory;
final class UnreachableStatementNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    /**
     * @readonly
     * @var string
     */
    private $filePath;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory
     */
    private $scopeFactory;
    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver, string $filePath, ScopeFactory $scopeFactory)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->filePath = $filePath;
        $this->scopeFactory = $scopeFactory;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof StmtsAwareInterface && !$node instanceof ClassLike && !$node instanceof Declare_) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        $isPassedUnreachableStmt = \false;
        $mutatingScope = $node->getAttribute(AttributeKey::SCOPE);
        $mutatingScope = $mutatingScope instanceof MutatingScope ? $mutatingScope : $this->scopeFactory->createFromFile($this->filePath);
        foreach ($node->stmts as $stmt) {
            if ($stmt->getAttribute(AttributeKey::IS_UNREACHABLE) === \true) {
                $isPassedUnreachableStmt = \true;
                continue;
            }
            if ($isPassedUnreachableStmt) {
                $stmt->setAttribute(AttributeKey::IS_UNREACHABLE, \true);
                $stmt->setAttribute(AttributeKey::SCOPE, $mutatingScope);
                $this->phpStanNodeScopeResolver->processNodes([$stmt], $this->filePath, $mutatingScope);
            }
        }
        return null;
    }
}
