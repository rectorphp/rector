<?php

/**
 * Future note: Not inside namespace on purpose to check last name Use_ used
 */
use \Company2\Bar;

final class SkipDocblockRenameDifferentNamespace
{
    /**
     * @param Bar $foo
     */
    public function importedClassesReferencedViaAlias($foo)
    {
    }
}
