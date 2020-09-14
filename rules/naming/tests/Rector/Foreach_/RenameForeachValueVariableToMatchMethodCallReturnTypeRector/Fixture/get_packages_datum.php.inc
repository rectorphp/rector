<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class GetPackagesData
{
    public function run()
    {
        $array = [];
        foreach ($this->getPackagesData() as $data) {
            $array[] = $data;
        }
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getPackagesData(): array
    {

    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

class GetPackagesData
{
    public function run()
    {
        $array = [];
        foreach ($this->getPackagesData() as $packageData) {
            $array[] = $packageData;
        }
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getPackagesData(): array
    {

    }
}

?>
