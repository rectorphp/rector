<?php

namespace Rector\Tests\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector\Fixture;

function singleTryCatch()
{
    try {
        // Some code...
    } catch (ExceptionType1|ExceptionType2 $e) {
        // Code to handle the exception
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector\Fixture;

function singleTryCatch()
{
    try {
        // Some code...
    } catch (ExceptionType1 $e) {
        // Code to handle the exception
    } catch (ExceptionType2 $e) {
        // Code to handle the exception
    }
}

?>
