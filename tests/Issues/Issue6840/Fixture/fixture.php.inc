<?php

namespace Rector\Core\Tests\Issues\Issue6840\Fixture;

class Fixture
{
    protected string $imageId;
    protected array $imageSize;

    public function __construct($imageId)
    {
        $this->imageId = $imageId;
        $this->imageSize = [];
    }
}
?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6840\Fixture;

class Fixture
{
    protected array $imageSize;

    public function __construct(protected $imageId)
    {
        $this->imageSize = [];
    }
}
?>
