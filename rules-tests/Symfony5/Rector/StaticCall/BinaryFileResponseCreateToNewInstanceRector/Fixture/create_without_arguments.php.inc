<?php

namespace Rector\Tests\Symfony5\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithoutArguments
{
    public function action()
    {
        $binaryFileResponse = BinaryFileResponse::create();
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony5\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithoutArguments
{
    public function action()
    {
        $binaryFileResponse = new BinaryFileResponse(null);
    }
}

?>
