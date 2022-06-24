<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source\FirstDependency;

class AClass extends FirstDependency{

    public function aMethod()
    {
       $this->go();
    }
}

?>
