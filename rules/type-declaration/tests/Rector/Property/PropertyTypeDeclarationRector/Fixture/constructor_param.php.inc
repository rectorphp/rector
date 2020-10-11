<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use stdClass;

class ConstructorParam
{
    private $stringValue;
    private $docBlockService;

    /**
     * @param stdClass $docBlockService
     */
    public function __construct(int $value, $docBlockService)
    {
        $this->stringValue = $value;
        $this->docBlockService = $docBlockService;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use stdClass;

class ConstructorParam
{
    /**
     * @var int
     */
    private $stringValue;
    /**
     * @var \stdClass
     */
    private $docBlockService;

    /**
     * @param stdClass $docBlockService
     */
    public function __construct(int $value, $docBlockService)
    {
        $this->stringValue = $value;
        $this->docBlockService = $docBlockService;
    }
}

?>
