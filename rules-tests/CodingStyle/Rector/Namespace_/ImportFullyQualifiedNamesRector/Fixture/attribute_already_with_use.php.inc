<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class AttributeAlreadyWithUse
{
    #[Route(path: 'access-denied', name: \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\ValueObject\RouteName::ACCESS_DENIED)]
    public function __invoke()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\ValueObject\RouteName;
use Symfony\Component\Routing\Annotation\Route;

final class AttributeAlreadyWithUse
{
    #[Route(path: 'access-denied', name: RouteName::ACCESS_DENIED)]
    public function __invoke()
    {
    }
}

?>
