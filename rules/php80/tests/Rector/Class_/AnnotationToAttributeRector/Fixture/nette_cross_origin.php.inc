<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NetteCrossOrigin
{
    /**
     * @crossOrigin
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NetteCrossOrigin
{
    #[\Nette\Application\Attributes\CrossOrigin]
    public function run()
    {
    }
}

?>
