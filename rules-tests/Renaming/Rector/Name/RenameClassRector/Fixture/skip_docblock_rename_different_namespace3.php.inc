<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use \Company2\{ Bar };

final class SkipDocblockRenameDifferentNamespace3
{
    /**
     * @param Bar $foo
     */
    public function importedClassViaSameNamespace($foo)
    {
    }
}
