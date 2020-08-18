<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\FixtureSymfony28;

use Symfony\Component\Routing\Router;

class UrlMatcherGenerate
{
    public function run()
    {
        $router = new Router();
        $router->generate('name', [], false);
        $router->generate('name', [], true);
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\FixtureSymfony28;

use Symfony\Component\Routing\Router;

class UrlMatcherGenerate
{
    public function run()
    {
        $router = new Router();
        $router->generate('name', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH);
        $router->generate('name', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

?>
