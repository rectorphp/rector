<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

// comment before include
include 'somefile.php';
// comment after include

?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

// comment before include

// comment after include

?>
