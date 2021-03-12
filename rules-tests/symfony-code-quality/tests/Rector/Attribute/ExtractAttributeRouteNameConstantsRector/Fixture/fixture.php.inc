<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class SomeClass
{
    #[Route(path: "path", name: "name")]
    public function run()
    {
    }

    #[Route(path: "path_2", name: "100_level")]
    public function go()
    {
    }
}

?>
-----
<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class SomeClass
{
    #[Route(path: "path", name: \App\ValueObject\Routing\RouteName::NAME)]
    public function run()
    {
    }

    #[Route(path: "path_2", name: \App\ValueObject\Routing\RouteName::ROUTE_100_LEVEL)]
    public function go()
    {
    }
}

?>
