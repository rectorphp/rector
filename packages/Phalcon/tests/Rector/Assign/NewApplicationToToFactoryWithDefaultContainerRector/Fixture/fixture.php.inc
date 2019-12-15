<?php

namespace Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector\Fixture;

class SomeClass {
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application($di);

        $response = $application->handle();
    }
}

?>
-----
<?php

namespace Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector\Fixture;

class SomeClass {
    public function run($di)
    {
        $container = new \Phalcon\Di\FactoryDefault();
        $application = new \Phalcon\Mvc\Application($container);

        $response = $application->handle($_SERVER["REQUEST_URI"]);
    }
}

?>
