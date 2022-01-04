<?php

namespace Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\Fixture;

return $e instanceof \Throwable;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\Fixture;

return ($throwable = $e) instanceof \Throwable || $throwable instanceof \Exception;

?>
