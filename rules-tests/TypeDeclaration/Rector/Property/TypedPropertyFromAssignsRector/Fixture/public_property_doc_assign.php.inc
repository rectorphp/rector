<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

use stdClass;

final class PublicPropertyDocAssign
{
    public $config = null;

    public function run()
    {
        $this->config = new stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

use stdClass;

final class PublicPropertyDocAssign
{
    /**
     * @var stdClass|null
     */
    public $config = null;

    public function run()
    {
        $this->config = new stdClass;
    }
}

?>
