<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class ParamScalar {
    /**
     * @param int|string|bool|float $value
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
     * @param scalar $value
     */
    public function getMessage($value)
    {
    }
}

?>
