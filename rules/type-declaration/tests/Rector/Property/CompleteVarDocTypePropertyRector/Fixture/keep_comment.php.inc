<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class KeepComment
{
    /**
     * @var string e.g. 123
     */
    private $ids;

    public function setIds()
    {
        $this->ids[] = 'hello';
        if (random_int(1, 100)) {
            $this->ids = 'hey';
        }
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class KeepComment
{
    /**
     * @var string[]|string|null e.g. 123
     */
    private $ids;

    public function setIds()
    {
        $this->ids[] = 'hello';
        if (random_int(1, 100)) {
            $this->ids = 'hey';
        }
    }
}

?>
