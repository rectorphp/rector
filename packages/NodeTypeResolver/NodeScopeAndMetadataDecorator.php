<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\NodeVisitor\FileNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor;
use Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
final class NodeScopeAndMetadataDecorator
{
    /**
     * @var string
     */
    private const OPTION_PRESERVE_ORIGINAL_NAMES = 'preserveOriginalNames';
    /**
     * @var string
     */
    private const OPTION_REPLACE_NODES = 'replaceNodes';
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
    public function __construct(\PhpParser\NodeVisitor\CloningVisitor $cloningVisitor, \Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor $functionMethodAndClassNodeVisitor, \Rector\NodeTypeResolver\NodeVisitor\NamespaceNodeVisitor $namespaceNodeVisitor, \Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver $phpStanNodeScopeResolver, \Rector\NodeTypeResolver\NodeVisitor\StatementNodeVisitor $statementNodeVisitor, \PhpParser\NodeVisitor\NodeConnectingVisitor $nodeConnectingVisitor, \Rector\NodeTypeResolver\NodeVisitor\FunctionLikeParamArgPositionNodeVisitor $functionLikeParamArgPositionNodeVisitor)
    {
        $this->cloningVisitor = $cloningVisitor;
        $this->functionMethodAndClassNodeVisitor = $functionMethodAndClassNodeVisitor;
        $this->namespaceNodeVisitor = $namespaceNodeVisitor;
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->statementNodeVisitor = $statementNodeVisitor;
        $this->nodeConnectingVisitor = $nodeConnectingVisitor;
        $this->functionLikeParamArgPositionNodeVisitor = $functionLikeParamArgPositionNodeVisitor;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function decorateNodesFromFile(\Rector\Core\ValueObject\Application\File $file, array $nodes) : array
    {
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $nodeTraverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver(null, [
            self::OPTION_PRESERVE_ORIGINAL_NAMES => \true,
            // required by PHPStan
            self::OPTION_REPLACE_NODES => \true,
        ]));
        /** @var Stmt[] $nodes */
        $nodes = $nodeTraverser->traverse($nodes);
        $smartFileInfo = $file->getSmartFileInfo();
        $nodes = $this->phpStanNodeScopeResolver->processNodes($nodes, $smartFileInfo);
        $nodeTraverserForPreservingName = new \PhpParser\NodeTraverser();
        $preservingNameResolver = new \PhpParser\NodeVisitor\NameResolver(null, [
            self::OPTION_PRESERVE_ORIGINAL_NAMES => \true,
            // this option would override old non-fqn-namespaced nodes otherwise, so it needs to be disabled
            self::OPTION_REPLACE_NODES => \false,
        ]);
        $nodeTraverserForPreservingName->addVisitor($preservingNameResolver);
        $nodes = $nodeTraverserForPreservingName->traverse($nodes);
        $nodeTraverserForFormatPreservePrinting = new \PhpParser\NodeTraverser();
        // needed also for format preserving printing
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->cloningVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->nodeConnectingVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->functionMethodAndClassNodeVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->namespaceNodeVisitor);
        $nodeTraverserForFormatPreservePrinting->addVisitor($this->functionLikeParamArgPositionNodeVisitor);
        $fileNodeVisitor = new \Rector\NodeTypeResolver\NodeVisitor\FileNodeVisitor($file);
        $nodeTraverserForFormatPreservePrinting->addVisitor($fileNodeVisitor);
        $nodes = $nodeTraverserForFormatPreservePrinting->traverse($nodes);
        // this split is needed, so nodes have names, classes and namespaces
        $nodeTraverserForStmtNodeVisitor = new \PhpParser\NodeTraverser();
        $nodeTraverserForStmtNodeVisitor->addVisitor($this->statementNodeVisitor);
        return $nodeTraverserForStmtNodeVisitor->traverse($nodes);
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
