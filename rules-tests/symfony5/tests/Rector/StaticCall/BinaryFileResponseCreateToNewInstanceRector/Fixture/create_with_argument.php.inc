<?php

namespace Rector\Symfony5\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithArgument
{
    public function action()
    {
        return BinaryFileResponse::create('some_file_path.txt');
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithArgument
{
    public function action()
    {
        return new BinaryFileResponse('some_file_path.txt');
    }
}

?>
