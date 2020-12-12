<?php

namespace Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector\Fixture;

class Fixture
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
class Fixture
{
    /**
     * @ORM\Id
     */
    public $id;
}

?>
