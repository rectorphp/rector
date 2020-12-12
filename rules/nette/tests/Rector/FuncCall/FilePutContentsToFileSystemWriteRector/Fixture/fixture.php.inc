<?php

namespace Rector\Nette\Tests\Rector\FuncCall\FilePutContentsToFileSystemWriteRector\Fixture;

class Fixture
{
    public function run()
    {
        file_put_contents('file.txt', 'content');

        file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\FilePutContentsToFileSystemWriteRector\Fixture;

class Fixture
{
    public function run()
    {
        \Nette\Utils\FileSystem::write('file.txt', 'content');

        file_put_contents('file.txt', 'content_to_append', FILE_APPEND);
    }
}

?>
