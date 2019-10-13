<?php

declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\AbstractRector\DoctrineTrait;

trait AbstractRectorTrait
{
    use AppliedRectorCollectorTrait;
    use DocBlockManipulatorTrait;
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
}
