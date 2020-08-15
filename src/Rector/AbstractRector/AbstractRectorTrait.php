<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Reporting\Rector\AbstractRector\NodeReportCollectorTrait;

trait AbstractRectorTrait
{
    use NodeReportCollectorTrait;

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

    protected function removeFinal(Class_ $class): void
    {
        $class->flags -= Class_::MODIFIER_FINAL;
    }
}
