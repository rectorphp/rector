<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Throwable;

class DefaultParentTryCatchThis extends SomeAjaxPresenter
{
    public function run($path)
    {
        $this->pDisallowAjax()->pRedirect($path);
    }
}

class SomeAjaxPresenter
{
    public function pDisallowAjax(): self
    {
        try {
            $value = 100;
        } catch (Throwable $throwable) {
        }

        return $this;
    }

    public function pRedirect($path)
    {

    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Throwable;

class DefaultParentTryCatchThis extends SomeAjaxPresenter
{
    public function run($path)
    {
        $this->pDisallowAjax();
        $this->pRedirect($path);
    }
}

class SomeAjaxPresenter
{
    public function pDisallowAjax(): self
    {
        try {
            $value = 100;
        } catch (Throwable $throwable) {
        }

        return $this;
    }

    public function pRedirect($path)
    {

    }
}

?>
