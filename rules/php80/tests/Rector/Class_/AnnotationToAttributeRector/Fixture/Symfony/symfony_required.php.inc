<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\Symfony;

class SymfonyRequired
{
    /**
     * @required
     */
    public function autowireSymfonyRequired()
    {
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture\Symfony;

class SymfonyRequired
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public function autowireSymfonyRequired()
    {
    }
}

?>
