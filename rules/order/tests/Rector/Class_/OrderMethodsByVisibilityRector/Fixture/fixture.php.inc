<?php

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\Fixture;

class Fixture
{
    protected function protectedFunctionName()
    {
    }
    private function privateFunctionName()
    {
    }
    final protected function finalProtectedFunctionName()
    {
    }
    static final protected function staticFinalProtectedFunctionName()
    {
    }
    public function publicFunctionName()
    {
    }
    final private function finalPrivateFunctionName()
    {
    }
    static final public function staticFinalPublicFunctionName()
    {
    }
    static protected function staticProtectedFunctionName()
    {
    }
    static private function staticPrivateFunctionName()
    {
    }
    static public function staticPublicFunctionName()
    {
    }
    final public function finalPublicFunctionName()
    {
    }
    static final private function staticFinalPrivateFunctionName()
    {
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector\Fixture;

class Fixture
{
    public function publicFunctionName()
    {
    }
    final public function finalPublicFunctionName()
    {
    }
    static public function staticPublicFunctionName()
    {
    }
    static final public function staticFinalPublicFunctionName()
    {
    }
    protected function protectedFunctionName()
    {
    }
    final protected function finalProtectedFunctionName()
    {
    }
    static protected function staticProtectedFunctionName()
    {
    }
    static final protected function staticFinalProtectedFunctionName()
    {
    }
    private function privateFunctionName()
    {
    }
    final private function finalPrivateFunctionName()
    {
    }
    static private function staticPrivateFunctionName()
    {
    }
    static final private function staticFinalPrivateFunctionName()
    {
    }
}

?>
