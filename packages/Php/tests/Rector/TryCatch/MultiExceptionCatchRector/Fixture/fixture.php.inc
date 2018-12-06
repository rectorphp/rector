<?php

namespace Rector\Php\Tests\Rector\TryCatch\MultiExceptionCatchRector\Wrong;

function multiExceptionCatch()
{
    try {
        // Some code...
    } catch (ExceptionType1 $e) {
        // Code to handle the exception
    } catch (ExceptionType2 $e) {
        // Code to handle the exception
    } catch (Exception $e) {
        // ...
    }


    try {
        // Some code...
    } catch (ExceptionType1 $e) {
        // Code to handle the exception
    } catch (ExceptionType2 $e) {
        $differentContent = 'hey';
    }


    try {
        // Some code...
    } catch (ExceptionType1 $e) {
        $e = 1;
    } catch (ExceptionType2 $e2) {
        $e = 1;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\TryCatch\MultiExceptionCatchRector\Wrong;

function multiExceptionCatch()
{
    try {
        // Some code...
    } catch (ExceptionType1|ExceptionType2 $e) {
        // Code to handle the exception
    } catch (Exception $e) {
        // ...
    }


    try {
        // Some code...
    } catch (ExceptionType1 $e) {
        // Code to handle the exception
    } catch (ExceptionType2 $e) {
        $differentContent = 'hey';
    }


    try {
        // Some code...
    } catch (ExceptionType1|ExceptionType2 $e) {
        $e = 1;
    }
}

?>
