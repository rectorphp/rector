<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\AutoImportNamesParameter;

use NotExistingNamespace\ClassName as Alias;

if (false) {
    $someAlias = new Alias();
}
