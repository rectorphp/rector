<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source as Assert;

final class EntityColumnAndAssertChoice
{
    /**
     * @Assert\GenericAnnotation({"php5", "php7", "php8"})
     * @Assert\GenericAnnotation(choices={"5.0", "5.1", "id"="5.2"})
     * @Assert\GenericAnnotation(choices={2, 3, 5, 7, 11, 13, 17, 19})
     */
    public $primeNumbers;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source as Assert;

final class EntityColumnAndAssertChoice
{
    #[Assert\GenericAnnotation(some: ['php5', 'php7', 'php8'])]
    #[Assert\GenericAnnotation(choices: ['5.0', '5.1', 'id' => '5.2'])]
    #[Assert\GenericAnnotation(choices: [2, 3, 5, 7, 11, 13, 17, 19])]
    public $primeNumbers;
}

?>
