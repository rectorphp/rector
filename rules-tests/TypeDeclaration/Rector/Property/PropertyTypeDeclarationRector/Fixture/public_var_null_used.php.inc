<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use stdClass;

class PublicVarNullUsed
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

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use stdClass;

class PublicVarNullUsed
{
    /**
     * @var null|\stdClass
     */
    public $config = null;

    public function run()
    {
        $this->config = new stdClass;
    }
}

?>