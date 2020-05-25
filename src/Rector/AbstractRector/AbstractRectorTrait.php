<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\ChangesReporting\Rector\AbstractRector\NotifyingRemovingNodeTrait;
use Rector\Doctrine\AbstractRector\DoctrineTrait;
use Rector\PostRector\Rector\AbstractRector\NodeCommandersTrait;

trait AbstractRectorTrait
{
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
    use NotifyingRemovingNodeTrait;
    use NodeCommentingTrait;

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

    protected function removeFinal(Class_ $node): void
    {
        $node->flags -= Class_::MODIFIER_FINAL;
    }
}
