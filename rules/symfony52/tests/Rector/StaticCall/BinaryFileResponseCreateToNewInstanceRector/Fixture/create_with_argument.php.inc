<?php

namespace Rector\Symfony52\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithArgument
{
    public function action()
    {
        $form = BinaryFileResponse::create('some_filename');
    }
}

?>
-----
<?php

namespace Rector\Symfony52\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithArgument
{
    public function action()
    {
        $form = new BinaryFileResponse('some_filename');
    }
}

?>
