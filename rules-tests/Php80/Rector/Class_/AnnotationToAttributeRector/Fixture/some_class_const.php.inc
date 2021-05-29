<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class SomeClassConst
{
    /**
     * @Assert\Choice(EntityColumnAndAssertChoice::class)
     */
    public $phpVersion;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class SomeClassConst
{
    #[\Symfony\Component\Validator\Constraints\Choice(EntityColumnAndAssertChoice::class)]
    public $phpVersion;
}

?>
