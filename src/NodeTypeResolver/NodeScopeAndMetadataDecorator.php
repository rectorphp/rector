<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use Rector\NodeTypeResolver\PHPStan\Scope\PHPStanNodeScopeResolver;
use Rector\PhpParser\NodeTraverser\FileWithoutNamespaceNodeTraverser;
final class NodeScopeAndMetadataDecorator
{
    /**
     * @readonly
     */
    private PHPStanNodeScopeResolver $phpStanNodeScopeResolver;
    /**
     * @readonly
     */
    private FileWithoutNamespaceNodeTraverser $fileWithoutNamespaceNodeTraverser;
    /**
     * @readonly
     */
    private NodeTraverser $nodeTraverser;
    public function __construct(CloningVisitor $cloningVisitor, PHPStanNodeScopeResolver $phpStanNodeScopeResolver, FileWithoutNamespaceNodeTraverser $fileWithoutNamespaceNodeTraverser)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->fileWithoutNamespaceNodeTraverser = $fileWithoutNamespaceNodeTraverser;
        // needed for format preserving printing
        $this->nodeTraverser = new NodeTraverser($cloningVisitor);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function decorateNodesFromFile(string $filePath, array $stmts) : array
    {
        $stmts = $this->fileWithoutNamespaceNodeTraverser->traverse($stmts);
        $stmts = $this->phpStanNodeScopeResolver->processNodes($stmts, $filePath);
        return $this->nodeTraverser->traverse($stmts);
    }
}
