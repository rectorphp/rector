<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;

final class ConfigurationWithStringStringArray
{
    public function getSome(): array
    {
        return [
            DimFetchAssignToMethodCallRector::class => [
                DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => [
                    new DimFetchAssignToMethodCall(
                        'Nette\Application\Routers\RouteList',
                        'Nette\Application\Routers\Route',
                        'addRoute'
                    ),
                ],
            ],
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector;
use Rector\Transform\ValueObject\DimFetchAssignToMethodCall;

final class ConfigurationWithStringStringArray
{
    /**
     * @return array<class-string<\Rector\Transform\Rector\Assign\DimFetchAssignToMethodCallRector>, array<string, \Rector\Transform\ValueObject\DimFetchAssignToMethodCall[]>>
     */
    public function getSome(): array
    {
        return [
            DimFetchAssignToMethodCallRector::class => [
                DimFetchAssignToMethodCallRector::DIM_FETCH_ASSIGN_TO_METHOD_CALL => [
                    new DimFetchAssignToMethodCall(
                        'Nette\Application\Routers\RouteList',
                        'Nette\Application\Routers\Route',
                        'addRoute'
                    ),
                ],
            ],
        ];
    }
}

?>
