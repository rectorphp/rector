<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class PropertyAssign
{
    private $eventDispatcher;

    public function __construct(\EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class PropertyAssign
{
    /**
     * @var \EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(\EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
