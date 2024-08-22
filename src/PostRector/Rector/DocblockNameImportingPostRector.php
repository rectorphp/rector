<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter;
use Rector\PostRector\Guard\AddUseStatementGuard;
final class DocblockNameImportingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNameImporter
     */
    private $docBlockNameImporter;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\PostRector\Guard\AddUseStatementGuard
     */
    private $addUseStatementGuard;
    public function __construct(DocBlockNameImporter $docBlockNameImporter, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->docBlockNameImporter = $docBlockNameImporter;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    /**
     * @return \PhpParser\Node|int|null
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof Stmt && !$node instanceof Param) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $hasDocChanged = $this->docBlockNameImporter->importNames($phpDocInfo->getPhpDocNode(), $node);
        if (!$hasDocChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts) : bool
    {
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
}
