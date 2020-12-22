<?php

namespace Rector\Phalcon\Tests\Rector\MethodCall\AddRequestToHandleMethodCallRector\Fixture;

class Fixture
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application();
        $response = $application->handle();
    }
}

?>
-----
<?php

namespace Rector\Phalcon\Tests\Rector\MethodCall\AddRequestToHandleMethodCallRector\Fixture;

class Fixture
{
    public function run($di)
    {
        $application = new \Phalcon\Mvc\Application();
        $response = $application->handle($_SERVER['REQUEST_URI']);
    }
}

?>
