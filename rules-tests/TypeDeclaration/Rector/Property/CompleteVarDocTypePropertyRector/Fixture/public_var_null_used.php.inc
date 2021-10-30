<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

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

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

use stdClass;

class PublicVarNullUsed
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
