<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser;
use Rector\Core\PHPStan\NodeVisitor\UnreachableStatementNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory;
final class NodeScopeAndMetadataDecorator
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\ScopeFactory
     */
    private $scopeFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser
     */
    private $fileWithoutNamespaceNodeTraverser;
    /**
     * @readonly
     * @var \PhpParser\NodeTraverser
     */
    private $nodeTraverser;
    public function __construct(CloningVisitor $cloningVisitor, PHPStanNodeScopeResolver $phpStanNodeScopeResolver, FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor, ScopeFactory $scopeFactory, FileWithoutNamespaceNodeTraverser $fileWithoutNamespaceNodeTraverser)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->scopeFactory = $scopeFactory;
        $this->fileWithoutNamespaceNodeTraverser = $fileWithoutNamespaceNodeTraverser;
        $this->nodeTraverser = new NodeTraverser();
        // needed for format preserving printing
        $this->nodeTraverser->addVisitor($cloningVisitor);
        $this->nodeTraverser->addVisitor($functionLikeParamArgPositionNodeVisitor);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateNodesFromFile(string $filePath, array $stmts) : array
    {
        $stmts = $this->fileWithoutNamespaceNodeTraverser->traverse($stmts);
        $stmts = $this->phpStanNodeScopeResolver->processNodes($stmts, $filePath);
        if ($this->phpStanNodeScopeResolver->hasUnreachableStatementNode()) {
            $unreachableStatementNodeVisitor = new UnreachableStatementNodeVisitor($this->phpStanNodeScopeResolver, $filePath, $this->scopeFactory);
            $this->nodeTraverser->addVisitor($unreachableStatementNodeVisitor);
            $stmts = $this->nodeTraverser->traverse($stmts);
            /**
             * immediate remove UnreachableStatementNodeVisitor after traverse to avoid
             * re-used in nodeTraverser property in next file
             */
            $this->nodeTraverser->removeVisitor($unreachableStatementNodeVisitor);
            // next file must be init hasUnreachableStatementNode to be false again
            $this->phpStanNodeScopeResolver->resetHasUnreachableStatementNode();
            return $stmts;
        }
        return $this->nodeTraverser->traverse($stmts);
    }
}
