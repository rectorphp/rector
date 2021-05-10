<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeCollector\NodeVisitor\NodeCollectorNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FileNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class NodeScopeAndMetadataDecorator
{
    /**
     * @var \PhpParser\NodeVisitor\CloningVisitor
     */
    private $cloningVisitor;
    /**
     * @var \Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor
     */
    private $functionMethodAndClassNodeVisitor;
    /**
     * @var \Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor
     */
    private $namespaceNodeVisitor;
    /**
     * @var \Rector\NodeCollector\NodeVisitor\NodeCollectorNodeVisitor
     */
    private $nodeCollectorNodeVisitor;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor
     */
    private $statementNodeVisitor;
    /**
     * @var \PhpParser\NodeVisitor\NodeConnectingVisitor
     */
    private $nodeConnectingVisitor;
    /**
     * @var \Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor
     */
    private $functionLikeParamArgPositionNodeVisitor;
    public function __construct(\PhpParser\NodeVisitor\CloningVisitor $cloningVisitor, \Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor $functionMethodAndClassNodeVisitor, \Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor $namespaceNodeVisitor, \Rector\NodeCollector\NodeVisitor\NodeCollectorNodeVisitor $nodeCollectorNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver $phpStanNodeScopeResolver, \Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor $statementNodeVisitor, \PhpParser\NodeVisitor\NodeConnectingVisitor $nodeConnectingVisitor, \Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor)
    {
        $this->cloningVisitor = $cloningVisitor;
        $this->functionMethodAndClassNodeVisitor = $functionMethodAndClassNodeVisitor;
        $this->namespaceNodeVisitor = $namespaceNodeVisitor;
        $this->nodeCollectorNodeVisitor = $nodeCollectorNodeVisitor;
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->statementNodeVisitor = $statementNodeVisitor;
        $this->nodeConnectingVisitor = $nodeConnectingVisitor;
        $this->functionLikeParamArgPositionNodeVisitor = $functionLikeParamArgPositionNodeVisitor;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function decorateNodesFromFile(\Rector\Core\ValueObject\Application\File $file, array $nodes, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : array
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver(null, [
            'preserveOriginalNames' => \true,
            // required by PHPStan
            'replaceNodes' => \true,
        ]));
        $nodes = $nodeTraverser->traverse($nodes);
        $nodes = $this->phpStanNodeScopeResolver->processNodes($nodes, $smartFileInfo);
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $preservingNameResolver = new \PhpParser\NodeVisitor\NameResolver(null, [
            'preserveOriginalNames' => \true,
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            'replaceNodes' => \false,
        ]);
        $nodeTraverser->addVisitor($preservingNameResolver);
        $nodes = $nodeTraverser->traverse($nodes);
        $nodeTraverser = new \PhpParser\NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverser->addVisitor($this->cloningVisitor);
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverser->addVisitor($this->functionLikeParamArgPositionNodeVisitor);
        $fileNodeVisitor = new \Rector\NodeTypeResolver\NodeVisitor\FileNodeVisitor($file);
        $nodeTraverser->addVisitor($fileNodeVisitor);
        $nodes = $nodeTraverser->traverse($nodes);
        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($this->statementNodeVisitor);
        $nodeTraverser->addVisitor($this->nodeCollectorNodeVisitor);
        return $nodeTraverser->traverse($nodes);
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function decorateNodesFromString(array $nodes) : array
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverser->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverser->addVisitor($this->statementNodeVisitor);
        return $nodeTraverser->traverse($nodes);
    }
}
