<?php

namespace Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector\Fixture;

class Fixture
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application($di);
    }
}

?>
-----
<?php

namespace Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector\Fixture;

class Fixture
{
    public function run($di)
    {
        $container = new \Phalcon\Di\FactoryDefault();
        $application = new \Phalcon\Mvc\Application($container);
    }
}

?>
