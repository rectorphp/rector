<?php

namespace Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @doto àreprendre et refactorer
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

namespace Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture;

/**
 * @doto àreprendre et refactorer
 * @see \Rector\Tests\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector\Fixture\SurroundedDocTest
 */
class SurroundedDoc
{
}

class SurroundedDocTest extends \PHPUnit\Framework\TestCase
{
}

?>
