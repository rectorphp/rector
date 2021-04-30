<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class ParamScalar {
    /**
     * @param int|string|bool|float
     */
    public function getMessage($value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class ParamScalar {
    /**
     * @param scalar
     */
    public function getMessage($value)
    {
    }
}

?>
