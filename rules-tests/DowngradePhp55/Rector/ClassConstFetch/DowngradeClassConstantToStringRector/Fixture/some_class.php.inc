<?php

namespace Rector\Tests\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector\Fixture;

final class SomeClass
{
}

echo SomeClass::class;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector\Fixture;

final class SomeClass
{
}

echo 'Rector\Tests\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector\Fixture\SomeClass';

?>
