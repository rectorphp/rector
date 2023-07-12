<?php

namespace Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeFixture extends TestCase
{
    /**
     * @testWith ["foo"]
     *           ["bar"]
     */
    public function testOne(): void
    {
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeFixture extends TestCase
{
    #[\PHPUnit\Framework\Attributes\TestWith(['foo'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['bar'])]
    public function testOne(): void
    {
    }
}

?>
