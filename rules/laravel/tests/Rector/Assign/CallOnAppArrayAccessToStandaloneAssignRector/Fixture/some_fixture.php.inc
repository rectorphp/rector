<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector\Fixture;

class SomeFixture
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    public function run()
    {
        $validator = $this->app['validator']->make('...');
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector\Fixture;

class SomeFixture
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    public function run()
    {
        /** @var \Illuminate\Validation\Factory $validationFactory */
        $validationFactory = $this->app['validator'];
        $validator = $validationFactory->make('...');
    }
}

?>
