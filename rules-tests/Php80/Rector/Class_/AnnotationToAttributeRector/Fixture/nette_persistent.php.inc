<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NettePersistent
{
    /**
     * @persistent
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NettePersistent
{
    #[\Nette\Application\Attributes\Persistent]
    public function run()
    {
    }
}

?>
