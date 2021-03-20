<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertRange
{
    /**
     * @Assert\Range(
     *      min=120,
     *      max=180,
     *      minMessage="You must be at least {{ limit }}cm tall to enter",
     *      maxMessage="You cannot be taller than {{ limit }}cm to enter"
     * )
     */
    protected $height;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class AssertRange
{
    #[\Symfony\Component\Validator\Constraints\Range(min: 120, max: 180, minMessage: 'You must be at least {{ limit }}cm tall to enter', maxMessage: 'You cannot be taller than {{ limit }}cm to enter')]
    protected $height;
}

?>
