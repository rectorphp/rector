<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
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
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
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
    public function __construct(\PhpParser\NodeVisitor\CloningVisitor $cloningVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver $phpStanNodeScopeResolver, \PhpParser\NodeVisitor\NodeConnectingVisitor $nodeConnectingVisitor, \Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor)
    {
        $this->cloningVisitor = $cloningVisitor;
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
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
        $nodeTraverser = new \PhpParser\NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->cloningVisitor);
        // this one has to be run again to re-connect nodes with new attributes
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->functionLikeParamArgPositionNodeVisitor);
        return $nodeTraverser->traverse($stmts);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateStmtsFromString(array $stmts) : array
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        return $nodeTraverser->traverse($stmts);
    }
}
