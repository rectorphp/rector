<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

$files = glob(__DIR__ . '/expect*.php');
// comment before include
include array_pop($files);
// comment after include

?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

$files = glob(__DIR__ . '/expect*.php');
// comment before include

// comment after include

?>
