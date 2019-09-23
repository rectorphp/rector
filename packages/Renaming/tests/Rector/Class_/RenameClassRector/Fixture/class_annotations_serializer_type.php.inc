<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

class ClassAnnotationsSerializerIterableType
{
    /**
     * @Serializer\Type("array<Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass>")
     */
    public $flights = [];
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

class ClassAnnotationsSerializerIterableType
{
    /**
     * @Serializer\Type("array<Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass>")
     */
    public $flights = [];
}

?>
