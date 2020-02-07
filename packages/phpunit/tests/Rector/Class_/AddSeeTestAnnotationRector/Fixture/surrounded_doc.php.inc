<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @TODO àreprendre et refactorer
 */
class SurroundedDoc
{
}

class SurroundedDocTest extends \PHPUnit\Framework\TestCase
{
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @TODO àreprendre et refactorer
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\Fixture\SurroundedDocTest
 */
class SurroundedDoc
{
}

class SurroundedDocTest extends \PHPUnit\Framework\TestCase
{
}

?>
