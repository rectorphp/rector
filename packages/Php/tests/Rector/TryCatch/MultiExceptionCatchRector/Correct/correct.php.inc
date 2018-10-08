<?php declare(strict_types=1);

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
