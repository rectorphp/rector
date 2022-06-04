<?php

/**
 * Future note: Not inside namespace on purpose to check last name GroupUse_ used
 */

use \Company2\{ Bar };

final class SkipDocblockRenameDifferentNamespace4
{
    /**
     * @param Bar $foo
     */
    public function importedClassViaSameNamespace($foo)
    {
    }
}
