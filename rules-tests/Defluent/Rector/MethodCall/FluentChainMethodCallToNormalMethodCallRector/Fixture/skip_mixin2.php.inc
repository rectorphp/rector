<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

/**
 * @mixin \Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture\SkipMixin2
 */
class SkipMixin2
{
    public function someFunction()
    {
        $this->obj = $this;
        $this->obj->run()->execute();
    }

    public function run()
    {
        return $this;
    }

    public function execute()
    {
        return $this;
    }
}

?>
