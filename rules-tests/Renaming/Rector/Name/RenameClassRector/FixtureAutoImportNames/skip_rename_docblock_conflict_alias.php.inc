<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use CompanyB\Foo as Storage;

class SkipRenameDocblockConflictAlias
{
    /**
     * @var Storage
     */
    protected $storage;
}

?>
