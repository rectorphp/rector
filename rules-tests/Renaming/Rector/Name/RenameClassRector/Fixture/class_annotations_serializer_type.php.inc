<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

class ClassAnnotationsSerializerIterableType
{
    /**
     * @Serializer\Type("array<Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass>")
     */
    public $flights = [];
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

class ClassAnnotationsSerializerIterableType
{
    /**
     * @Serializer\Type("array<Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass>")
     */
    public $flights = [];
}

?>
