<?php

namespace Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\Fixture;

use Throwable;

return something() && $e instanceof Throwable;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector\Fixture;

use Throwable;

return something() && (($throwable = $e) instanceof Throwable || $throwable instanceof \Exception);

?>
