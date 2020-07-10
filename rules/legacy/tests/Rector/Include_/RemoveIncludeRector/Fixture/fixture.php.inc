<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

file_put_contents('somefile.php', '<?php // some comment');
// comment before include
include 'somefile.php';
// comment after include
unlink('somefile.php');
?>
-----
<?php

namespace Rector\Legacy\Tests\Rector\Include_\RemoveIncludeRector\Fixture;

file_put_contents('somefile.php', '<?php // some comment');
// comment before include

// comment after include
unlink('somefile.php');
?>
