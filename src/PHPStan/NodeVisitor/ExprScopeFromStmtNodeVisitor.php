<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Node\VirtualNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use PHPStan\Analyser\MutatingScope;
final class ExprScopeFromStmtNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @readonly
     * @var \PHPStan\Analyser\MutatingScope
     */
    private $mutatingScope;
    /**
     * @var \PhpParser\Node\Stmt|null
     */
    private $currentStmt;
    public function __construct(MutatingScope $mutatingScope)
    {
        $this->mutatingScope = $mutatingScope;
    }
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof Stmt) {
            $this->currentStmt = $node;
            return null;
        }
        if ($node instanceof VirtualNode) {
            return null;
        }
        if (!$node instanceof Expr || $node->getAttribute(AttributeKey::EXPRESSION_DEPTH) < 2) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope) {
            return null;
        }
        // too deep Expr, eg: $$param = $$bar = self::decodeValue($result->getItem()->getTextContent());
        $scope = $this->currentStmt instanceof Stmt ? $this->currentStmt->getAttribute(AttributeKey::SCOPE) : $this->mutatingScope;
        $scope = $scope instanceof Scope ? $scope : $this->mutatingScope;
        $node->setAttribute(AttributeKey::SCOPE, $scope);
        return null;
    }
}
