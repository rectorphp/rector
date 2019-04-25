<?php

declare(strict_types=1);

namespace Rector\RemovingStatic;

use PhpParser\Node\Stmt\Class_;

final class UniqueObjectOrServiceDetector
{
    public function isUniqueObject(Class_ $class): bool
    {
        // ideas:
        // hook in container?
        // has scalar arguments?
        // is created by new X in the code? → add "NewNodeCollector"

        // fallback for now :)
        return true;
    }
}
