<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector\Fixture;

return ! str_starts_with($haystack, $needle);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector\Fixture;

return strncmp($haystack, $needle, strlen($needle)) !== 0;

?>
