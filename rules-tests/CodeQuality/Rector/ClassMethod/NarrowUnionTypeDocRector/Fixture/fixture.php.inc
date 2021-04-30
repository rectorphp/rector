<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class Fixture {
    /**
     * @param object|DateTime $message
     */
    public function getMessage(object $message)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class Fixture {
    /**
     * @param DateTime $message
     */
    public function getMessage(object $message)
    {
    }
}

?>
