<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Source\ContainerExceptionInterface;

interface ExceptionInterface extends ContainerExceptionInterface, \Throwable
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Source\ContainerExceptionInterface;

interface ExceptionInterface extends ContainerExceptionInterface
{
}

?>
