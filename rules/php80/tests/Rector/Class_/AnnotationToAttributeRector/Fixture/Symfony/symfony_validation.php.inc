<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\Symfony;

use Symfony\Component\Validator\Constraints as Assert;

class SymfonyValidation
{
    /**
     * @Assert\DivisibleBy(0.25)
     */
    protected $weight;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\Symfony;

use Symfony\Component\Validator\Constraints as Assert;

class SymfonyValidation
{
    /**
     * #[\Symfony\Component\Validator\Constraints\DivisibleBy(0.25)]
     */
    protected $weight;
}

?>
