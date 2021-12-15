<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use stdClass;

final class PublicPropertyDoc
{
    public $config;

    public function __construct()
    {
        $this->config = new stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

use stdClass;

final class PublicPropertyDoc
{
    /**
     * @var stdClass
     */
    public $config;

    public function __construct()
    {
        $this->config = new stdClass;
    }
}

?>
