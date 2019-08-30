<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

class ClassAnnotationsSerializerIterableType
{
    /**
     * @Serializer\Type("array<Rector\Tests\Rector\Class_\RenameClassRector\Source\OldClass>")
     */
    public $flights = [];
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

class ClassAnnotationsSerializerIterableType
{
    /**
     * @Serializer\Type("array<Rector\Tests\Rector\Class_\RenameClassRector\Source\NewClass>")
     */
    public $flights = [];
}

?>
