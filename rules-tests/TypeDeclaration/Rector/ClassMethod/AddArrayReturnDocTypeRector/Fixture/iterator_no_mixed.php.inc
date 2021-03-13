<?php

use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;

class IteratorNoMixed
{
    public function getFiles()
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    private function yieldFilesFromDirectory(string $directory)
    {
        return StaticFixtureFinder::yieldDirectoryExclusively($directory);
    }
}

?>
-----
<?php

use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;

class IteratorNoMixed
{
    public function getFiles()
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
     */
    private function yieldFilesFromDirectory(string $directory)
    {
        return StaticFixtureFinder::yieldDirectoryExclusively($directory);
    }
}

?>
