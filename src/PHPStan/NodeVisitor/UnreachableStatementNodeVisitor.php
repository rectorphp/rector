<?php

declare (strict_types=1);
namespace Rector\PHPStan\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
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
     * @var \PHPStan\Analyser\MutatingScope
     */
    private $mutatingScope;
    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver, string $filePath, MutatingScope $mutatingScope)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->filePath = $filePath;
        $this->mutatingScope = $mutatingScope;
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
        $mutatingScope = $this->resolveScope($node->getAttribute(AttributeKey::SCOPE));
        foreach ($node->stmts as $stmt) {
            $hasMutatingScope = $stmt->getAttribute(AttributeKey::SCOPE) instanceof MutatingScope;
            if (!$hasMutatingScope) {
                $stmt->setAttribute(AttributeKey::SCOPE, $mutatingScope);
                $this->phpStanNodeScopeResolver->processNodes([$stmt], $this->filePath, $mutatingScope);
            }
            if ($stmt->getAttribute(AttributeKey::IS_UNREACHABLE) === \true) {
                $isPassedUnreachableStmt = \true;
                continue;
            }
            if ($isPassedUnreachableStmt) {
                $stmt->setAttribute(AttributeKey::IS_UNREACHABLE, \true);
            }
        }
        return null;
    }
    private function resolveScope(?Scope $mutatingScope) : MutatingScope
    {
        return $mutatingScope instanceof MutatingScope ? $mutatingScope : $this->mutatingScope;
    }
}
