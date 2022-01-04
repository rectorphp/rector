<?php

namespace Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\Fixture;

use Throwable;

return something() && ($e instanceof \Exception || $foo || $e instanceof Throwable);

?>
