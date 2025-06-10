<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
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
     * @var array<string, string>
     */
    private array $oldToNewClasses = [];
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, UseImportsRemover $useImportsRemover, RenamedNameCollector $renamedNameCollector)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->useImportsRemover = $useImportsRemover;
        $this->renamedNameCollector = $renamedNameCollector;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes) : array
    {
        if (!SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return $nodes;
        }
        foreach ($nodes as $node) {
            if ($node instanceof FileWithoutNamespace || $node instanceof Namespace_) {
                $removedUses = $this->renamedClassesDataCollector->getOldClasses();
                $node->stmts = $this->useImportsRemover->removeImportsFromStmts($node->stmts, $removedUses);
                break;
            }
        }
        return $nodes;
    }
    /**
     * @param Node[] $nodes
     * @return Stmt[]
     */
    public function afterTraverse(array $nodes) : array
    {
        $this->renamedNameCollector->reset();
        return $nodes;
    }
    public function shouldTraverse(array $stmts) : bool
    {
        $this->oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        return $this->oldToNewClasses !== [];
    }
}
