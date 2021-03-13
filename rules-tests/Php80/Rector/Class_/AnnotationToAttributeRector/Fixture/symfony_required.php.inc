<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

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

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequired
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public function autowireSymfonyRequired()
    {
    }
}

?>
