<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequiredProperty
{
    /**
     * @required
     */
    public $someProperty;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequiredProperty
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public $someProperty;
}

?>
