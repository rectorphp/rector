<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
final class NodeScopeAndMetadataDecorator
{
    /**
     * @readonly
     * @var \PhpParser\NodeVisitor\CloningVisitor
     */
    private $cloningVisitor;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor
     */
    private $namespaceNodeVisitor;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor
     */
    private $statementNodeVisitor;
    /**
     * @readonly
     * @var \PhpParser\NodeVisitor\NodeConnectingVisitor
     */
    private $nodeConnectingVisitor;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor
     */
    private $functionLikeParamArgPositionNodeVisitor;
    public function __construct(\PhpParser\NodeVisitor\CloningVisitor $cloningVisitor, \Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor $namespaceNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver $phpStanNodeScopeResolver, \Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor $statementNodeVisitor, \PhpParser\NodeVisitor\NodeConnectingVisitor $nodeConnectingVisitor, \Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor)
    {
        $this->cloningVisitor = $cloningVisitor;
        $this->namespaceNodeVisitor = $namespaceNodeVisitor;
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->statementNodeVisitor = $statementNodeVisitor;
        $this->nodeConnectingVisitor = $nodeConnectingVisitor;
        $this->functionLikeParamArgPositionNodeVisitor = $functionLikeParamArgPositionNodeVisitor;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateNodesFromFile(\Rector\Core\ValueObject\Application\File $file, array $stmts) : array
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $stmts = $this->phpStanNodeScopeResolver->processNodes($stmts, $smartFileInfo);
        $nodeTraverserForFormatPreservePrinting = new \PhpParser\NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->cloningVisitor);
        // this one has to be run again to re-connect nodes with new attributes
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->functionLikeParamArgPositionNodeVisitor);
        $stmts = $nodeTraverserForFormatPreservePrinting->traverse($stmts);
        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverserForStmtNodeVisitor = new \PhpParser\NodeTraverser();
        $nodeTraverserForStmtNodeVisitor->addVisitor($this->statementNodeVisitor);
        return $nodeTraverserForStmtNodeVisitor->traverse($stmts);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateStmtsFromString(array $stmts) : array
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->statementNodeVisitor);
        return $nodeTraverser->traverse($stmts);
    }
}
