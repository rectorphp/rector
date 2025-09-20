<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitor;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
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
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        if (!SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return $nodes;
        }
        foreach ($nodes as $node) {
            if ($node instanceof FileWithoutNamespace || $node instanceof Namespace_) {
                $removedUses = $this->renamedClassesDataCollector->getOldClasses();
                if ($this->useImportsRemover->removeImportsFromStmts($node, $removedUses)) {
                    $this->addRectorClassWithLine($node);
                }
                break;
            }
        }
        $this->renamedNameCollector->reset();
        return $nodes;
    }
    public function enterNode(Node $node): int
    {
        /**
         * We stop the traversal because all the work has already been done in the beforeTraverse() function
         *
         * Using STOP_TRAVERSAL is usually dangerous as it will stop the processing of all your nodes for all visitors
         * but since the PostFileProcessor is using direct new NodeTraverser() and traverse() for only a single
         * visitor per execution, using stop traversal here is safe,
         * ref https://github.com/rectorphp/rector-src/blob/fc1e742fa4d9861ccdc5933f3b53613b8223438d/src/PostRector/Application/PostFileProcessor.php#L59-L61
         */
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
