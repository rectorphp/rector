<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequired
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public function autowireSymfonyRequired()
    {
    }
}

?>
