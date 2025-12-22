<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitor;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\PhpParser\Node\FileNode;
use Rector\PostRector\Guard\AddUseStatementGuard;
use Rector\Renaming\Collector\RenamedNameCollector;
final class ClassRenamingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     */
    private RenamedClassesDataCollector $renamedClassesDataCollector;
    /**
     * @readonly
     */
    private UseImportsRemover $useImportsRemover;
    /**
     * @readonly
     */
    private RenamedNameCollector $renamedNameCollector;
    /**
     * @readonly
     */
    private AddUseStatementGuard $addUseStatementGuard;
    /**
     * @var array<string, string>
     */
    private array $oldToNewClasses = [];
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, UseImportsRemover $useImportsRemover, RenamedNameCollector $renamedNameCollector, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->useImportsRemover = $useImportsRemover;
        $this->renamedNameCollector = $renamedNameCollector;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    /**
     * @return \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\FileNode|int|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof FileNode) {
            // handle in Namespace_ node
            if ($node->isNamespaced()) {
                return null;
            }
            // handle here
            $removedUses = $this->renamedClassesDataCollector->getOldClasses();
            if ($this->useImportsRemover->removeImportsFromStmts($node, $removedUses)) {
                $this->addRectorClassWithLine($node);
            }
            $this->renamedNameCollector->reset();
            return $node;
        }
        if ($node instanceof Namespace_) {
            $removedUses = $this->renamedClassesDataCollector->getOldClasses();
            if ($this->useImportsRemover->removeImportsFromStmts($node, $removedUses)) {
                $this->addRectorClassWithLine($node);
            }
            $this->renamedNameCollector->reset();
            return $node;
        }
        // nothing else to handle here, as first 2 nodes we'll hit are handled above
        return NodeVisitor::STOP_TRAVERSAL;
    }
    public function shouldTraverse(array $stmts): bool
    {
        $this->oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($this->oldToNewClasses === []) {
            return \false;
        }
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
}
