<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use Message;

class ParamDouble
{
    /**
     * @var array $meta
     */
    private $meta = [];

    /**
     * @param array $meta
     *
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

class ParamDouble
{
    /**
     * @var array $meta
     */
    private $meta = [];

    /**
     * @param mixed[] $meta
     *
     * @return Message
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }
}

?>
