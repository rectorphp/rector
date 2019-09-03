<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

class SomeService
{
}

class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture\SomeServiceTest
 */
class SomeService
{
}

class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}

?>
