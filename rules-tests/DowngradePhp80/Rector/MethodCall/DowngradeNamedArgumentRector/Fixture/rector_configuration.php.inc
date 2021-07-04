<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

use Rector\Core\ValueObject\Configuration;

final class RectorConfiguration
{
    public function run(array $fileExtensions)
    {
        return new Configuration(isDryRun: true, fileExtensions: $fileExtensions);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\Fixture;

use Rector\Core\ValueObject\Configuration;

final class RectorConfiguration
{
    public function run(array $fileExtensions)
    {
        return new Configuration(true, true, false, 'console', $fileExtensions);
    }
}

?>
