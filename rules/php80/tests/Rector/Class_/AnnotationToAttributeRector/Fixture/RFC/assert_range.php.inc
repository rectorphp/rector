<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Symfony\Component\Validator\Constraints as Assert;

class AssertRange
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

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\RFC;

use Symfony\Component\Validator\Constraints as Assert;

class AssertRange
{
    #[\Symfony\Component\Validator\Constraints\Range(min: 120, max: 180, minMessage: 'You must be at least {{ limit }}cm tall to enter', maxMessage: 'You cannot be taller than {{ limit }}cm to enter')]
    protected $height;
}

?>
