<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Message;

class ParamNoDouble
{
    /**
     * @var array $meta
     */
    private $meta = [];

    /**
     * @return Message
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Message;

class ParamNoDouble
{
    /**
     * @var array $meta
     */
    private $meta = [];

    /**
     * @return Message
     * @param mixed[] $meta
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }
}

?>
