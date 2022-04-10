<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Config\RectorConfig;

return static function (RectorConfig $containerConfigurator): void {

};

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {

};

?>
