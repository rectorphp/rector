<?php

namespace Rector\Tests\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector\Fixture;

try {
    // Some code...
} catch (\Throwable $e) {
    // Code to handle the exception
} catch (AnotherException $e) {
    // ...
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector\Fixture;

try {
    // Some code...
} catch (\Throwable $e) {
    // Code to handle the exception
} catch (\Exception $e) {
    // Code to handle the exception
} catch (AnotherException $e) {
    // ...
}

?>
