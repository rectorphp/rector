<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * This is here
 */
class AddToDocBlock
{
}

class AddToDocBlockTest extends \PHPUnit\Framework\TestCase
{
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * This is here
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture\AddToDocBlockTest
 */
class AddToDocBlock
{
}

class AddToDocBlockTest extends \PHPUnit\Framework\TestCase
{
}

?>
