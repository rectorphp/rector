<?php

namespace Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector\Fixture;

use PHPUnit\Framework\TestCase;

final class TheMostComplexJson extends TestCase
{
    /**
     * @testWith [{"day": "monday", "conditions": "sunny"}, ["day", "conditions"]]
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

final class TheMostComplexJson extends TestCase
{
    #[\PHPUnit\Framework\Attributes\TestWith([['day' => 'monday', 'conditions' => 'sunny'], ['day', 'conditions']])]
     public function testOne(): void
     {
     }
}

?>
