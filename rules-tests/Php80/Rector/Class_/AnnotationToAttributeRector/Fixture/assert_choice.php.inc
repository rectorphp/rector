<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class EntityColumnAndAssertChoice
{
    /**
     * @Assert\Choice({"php5", "php7", "php8"})
     */
    public $phpVersion;

    /**
     * @Assert\Choice(choices={"5.0", "5.1", "id": "5.2"})
     */
    public $sfVersion;

    /**
     * @Assert\Choice(choices={2, 3, 5, 7, 11, 13, 17, 19})
     */
    public $primeNumbers;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class EntityColumnAndAssertChoice
{
    #[\Symfony\Component\Validator\Constraints\Choice(['php5', 'php7', 'php8'])]
    public $phpVersion;

    #[\Symfony\Component\Validator\Constraints\Choice(choices: ['5.0', '5.1', 'id' => '5.2'])]
    public $sfVersion;

    #[\Symfony\Component\Validator\Constraints\Choice(choices: [2, 3, 5, 7, 11, 13, 17, 19])]
    public $primeNumbers;
}

?>
