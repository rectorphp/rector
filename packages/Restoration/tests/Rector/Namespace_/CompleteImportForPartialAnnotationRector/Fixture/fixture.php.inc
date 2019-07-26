<?php

namespace Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector\Fixture;

class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
class SomeClass
{
    /**
     * @ORM\Id
     */
    public $id;
}

?>
