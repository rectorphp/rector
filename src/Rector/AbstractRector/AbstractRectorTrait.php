<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\ChangesReporting\Rector\AbstractRector\RectorChangeCollectorTrait;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Doctrine\AbstractRector\DoctrineTrait;

trait AbstractRectorTrait
{
    use RectorChangeCollectorTrait;
    use DoctrineTrait;
    use NodeTypeResolverTrait;
    use NameResolverTrait;
    use ConstFetchAnalyzerTrait;
    use BetterStandardPrinterTrait;
    use NodeCommandersTrait;
    use NodeFactoryTrait;
    use VisibilityTrait;
    use ValueResolverTrait;
    use CallableNodeTraverserTrait;
    use ComplexRemovalTrait;
    use NodeCollectorTrait;

    protected function isNonAnonymousClass(?Node $node): bool
    {
        if ($node === null) {
            return false;
        }

        if (! $node instanceof Class_) {
            return false;
        }

        $name = $this->getName($node);
        if ($name === null) {
            return false;
        }

        return ! Strings::contains($name, 'AnonymousClass');
    }

    /**
     * @param Closure|ClassMethod|Function_ $node
     */
    protected function removeStmt(Node $node, $key): void
    {
        if ($node->stmts === null) {
            throw new ShouldNotHappenException();
        }

        // notify about remove node
        $this->notifyNodeChangeFileInfo($node->stmts[$key]);

        unset($node->stmts[$key]);
    }
}
