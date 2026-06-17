<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use Override;
use PhpParser\Node;
use PhpParser\NodeVisitor;
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
    private RenamedNameCollector $renamedNameCollector;
    /**
     * @readonly
     */
    private AddUseStatementGuard $addUseStatementGuard;
    /**
     * @var array<string, string>
     */
    private array $oldToNewClasses = [];
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, RenamedNameCollector $renamedNameCollector, AddUseStatementGuard $addUseStatementGuard)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->renamedNameCollector = $renamedNameCollector;
        $this->addUseStatementGuard = $addUseStatementGuard;
    }
    /**
     * @return \Rector\PhpParser\Node\FileNode|int
     */
    public function enterNode(Node $node)
    {
        // the FileNode resolves the namespace-or-file placement internally
        if ($node instanceof FileNode) {
            // keep only the uses that were actually renamed
            $removedUses = array_values(array_filter($this->renamedClassesDataCollector->getOldClasses(), \Closure::fromCallable([$this->renamedNameCollector, 'has'])));
            if ($node->removeImports($removedUses)) {
                $this->addRectorClassWithLine($node);
            }
            $this->renamedNameCollector->reset();
            return $node;
        }
        // nothing else to handle here, as the first node we'll hit is handled above
        return NodeVisitor::STOP_TRAVERSAL;
    }
    #[Override]
    public function shouldTraverse(array $stmts): bool
    {
        $this->oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($this->oldToNewClasses === []) {
            return \false;
        }
        return $this->addUseStatementGuard->shouldTraverse($stmts, $this->getFile()->getFilePath());
    }
}
