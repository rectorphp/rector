<?php

namespace Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class SomeClass
{
    #[Route(path: "path", name: "name")]
    public function run()
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
}

?>
