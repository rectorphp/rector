<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\Fixture;

class SkipNonUnion {
    /**
     * @param DateTime $message
     */
    public function getMessage(object $message)
    {
    }
}

?>

