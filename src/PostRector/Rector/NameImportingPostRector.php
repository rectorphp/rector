<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\PostRector\Guard\AddUseStatementGuard;
final class NameImportingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     */
    private NameImporter $nameImporter;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @readonly
     */
    private AddUseStatementGuard $addUseStatementGuard;
    /**
     * @var array<Use_|GroupUse>
     */
    private array $currentUses = [];
    public function __construct(NameImporter $nameImporter, UseImportsResolver $useImportsResolver, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->nameImporter = $nameImporter;
        $this->useImportsResolver = $useImportsResolver;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    /**
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes) : array
    {
        $this->currentUses = $this->useImportsResolver->resolve();
        return $nodes;
    }
    public function enterNode(Node $node) : ?\PhpParser\Node
    {
        if (!$node instanceof FullyQualified) {
            return null;
        }
        return $this->nameImporter->importName($node, $this->getFile(), $this->currentUses);
    }
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts) : bool
    {
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
}
