<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use CompanyB\Queue;

$queue = app(\Queue::class);

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use CompanyB\Queue;

$queue = app(\Illuminate\Support\Facades\Queue::class);

?>
