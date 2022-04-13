<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\FixtureInlinePublic;

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

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\FixtureInlinePublic;

use stdClass;

final class PublicPropertyDocAssign
{
    public ?\stdClass $config = null;

    public function run()
    {
        $this->config = new stdClass;
    }
}

?>
