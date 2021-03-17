<?php

namespace Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

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

namespace Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * This is here
 * @see \Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture\AddToDocBlockTest
 */
class AddToDocBlock
{
}

class AddToDocBlockTest extends \PHPUnit\Framework\TestCase
{
}

?>
