<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source as Assert;

final class EntityColumnAndAssertChoice
{
    /**
     * @Assert\GenericAnnotation({"php5", "php7", "php8"})
     * @Assert\GenericAnnotation(choices={"5.0", "5.1", "id": "5.2"})
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
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(some: ['php5', 'php7', 'php8'])]
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(choices: ['5.0', '5.1', 'id' => '5.2'])]
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(choices: [2, 3, 5, 7, 11, 13, 17, 19])]
    public $primeNumbers;
}

?>
