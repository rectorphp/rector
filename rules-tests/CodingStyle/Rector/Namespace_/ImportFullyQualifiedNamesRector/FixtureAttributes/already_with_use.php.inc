<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureAttributes;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteAttribute
{
    #[Route(path: 'access-denied', name: \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\ValueObject\RouteName::ACCESS_DENIED)]
    public function __invoke()
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureAttributes;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\ValueObject\RouteName;
use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteAttribute
{
    #[Route(path: 'access-denied', name: RouteName::ACCESS_DENIED)]
    public function __invoke()
    {
    }
}

?>
