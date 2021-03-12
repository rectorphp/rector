<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureAttributes;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteAttribute
{
    #[Route(path: 'access-denied', name: \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\ValueObject\RouteName::ACCESS_DENIED)]
    public function __invoke()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureAttributes;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\ValueObject\RouteName;
use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteAttribute
{
    #[Route(path: 'access-denied', name: RouteName::ACCESS_DENIED)]
    public function __invoke()
    {
    }
}

?>
