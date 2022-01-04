<?php

namespace Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\Fixture;

use Throwable;

return something() && ($e instanceof Throwable || $e instanceof \Exception);

?>
