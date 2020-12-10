<?php

namespace Rector\Symfony52\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithoutArguments
{
    public function action()
    {
        $form = BinaryFileResponse::create();
    }
}

?>
-----
<?php

namespace Rector\Symfony52\Tests\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector\Fixture;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateWithoutArguments
{
    public function action()
    {
        $form = new BinaryFileResponse(null);
    }
}

?>
