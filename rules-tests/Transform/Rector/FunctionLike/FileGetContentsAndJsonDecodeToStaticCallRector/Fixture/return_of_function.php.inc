<?php

namespace Rector\Tests\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector\Fixture;

final class ReturnOfFunction
{
    public function load($filePath)
    {
        $fileGetContents = file_get_contents($filePath);
        return json_decode($fileGetContents, true);
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector\Fixture;

final class ReturnOfFunction
{
    public function load($filePath)
    {
        return \FileLoader::loadJson($filePath);
    }
}

?>
