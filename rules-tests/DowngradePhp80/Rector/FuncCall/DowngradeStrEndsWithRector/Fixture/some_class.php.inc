<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector\Fixture;

str_ends_with($haystack, $needle);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector\Fixture;

substr_compare($haystack, $needle, -strlen($needle)) === 0;

?>
