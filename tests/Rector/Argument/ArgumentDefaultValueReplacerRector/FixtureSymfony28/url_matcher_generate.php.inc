<?php

namespace Rector\Core\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector\FixtureSymfony28;

use Symfony\Component\Routing\Router;

class UrlMatcherGenerate
{
    public function run()
    {
        $router = new Router();
        $router->generate('name', [], 1);
        $router->generate('name', [], true);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector\FixtureSymfony28;

use Symfony\Component\Routing\Router;

class UrlMatcherGenerate
{
    public function run()
    {
        $router = new Router();
        $router->generate('name', [], 1);
        $router->generate('name', [], 0);
    }
}

?>
