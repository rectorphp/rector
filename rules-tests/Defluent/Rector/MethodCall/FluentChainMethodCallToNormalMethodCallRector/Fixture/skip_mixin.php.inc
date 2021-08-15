<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

/**
 * @mixin SkipMixin
 */
class SkipMixin
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
