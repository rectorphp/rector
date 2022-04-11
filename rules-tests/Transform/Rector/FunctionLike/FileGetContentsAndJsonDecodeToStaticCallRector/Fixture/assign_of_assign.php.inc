<?php

namespace Rector\Tests\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector\Fixture;

final class AssignOfAssign
{
    public function load($filePath)
    {
        $fileGetContents = file_get_contents($filePath);
        $anotherValue = json_decode($fileGetContents, true);

        return $anotherValue;
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector\Fixture;

final class AssignOfAssign
{
    public function load($filePath)
    {
        $anotherValue = \FileLoader::loadJson($filePath);

        return $anotherValue;
    }
}

?>
