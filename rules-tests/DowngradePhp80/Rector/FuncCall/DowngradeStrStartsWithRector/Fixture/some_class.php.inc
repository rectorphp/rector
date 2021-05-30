<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector\Fixture;

str_starts_with($haystack, $needle);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector\Fixture;

strncmp($haystack, $needle, strlen($needle)) === 0;

?>
