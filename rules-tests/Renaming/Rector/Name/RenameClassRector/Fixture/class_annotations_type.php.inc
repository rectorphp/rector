<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

final class ClassAnnotationsSilentType
{
    /**
     * @Serializer\Type("Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass")
     */
    public $time;
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

final class ClassAnnotationsSilentType
{
    /**
     * @Serializer\Type("Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass")
     */
    public $time;
}

?>
