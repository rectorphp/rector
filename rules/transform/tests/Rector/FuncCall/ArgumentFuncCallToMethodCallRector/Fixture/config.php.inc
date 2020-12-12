<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class SomeConfigController
{
    public function actionGet()
    {
        $value = config('value');
    }

    public function actionSet($value)
    {
        config(['value' => $value]);
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class SomeConfigController
{
    private \Illuminate\Contracts\Config\Repository $configRepository;
    public function __construct(\Illuminate\Contracts\Config\Repository $configRepository)
    {
        $this->configRepository = $configRepository;
    }
    public function actionGet()
    {
        $value = $this->configRepository->get('value');
    }

    public function actionSet($value)
    {
        $this->configRepository->set(['value' => $value]);
    }
}

?>
