<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\GetClassToInstanceOfRector\Fixture;

class SkipParentUsage extends SomeParent {
    public function test(object $other) {
       var_dump(parent::class);
       var_dump(get_class($other) === parent::class);
       var_dump($other instanceof parent);
    }
}

(new SkipParentUsage())->test(new SkipParentUsage());
