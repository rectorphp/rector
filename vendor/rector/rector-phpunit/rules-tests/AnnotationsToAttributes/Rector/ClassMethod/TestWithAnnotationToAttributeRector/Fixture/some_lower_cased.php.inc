<?php

namespace Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeLowerCased extends TestCase
{
    /**
     * @testwith ["rum"]
     * @testwith    ["fim"]
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

final class SomeLowerCased extends TestCase
{
    #[\PHPUnit\Framework\Attributes\TestWith(['rum'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['fim'])]
    public function testOne(): void
    {
    }
}

?>
