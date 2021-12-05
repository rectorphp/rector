<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class LocalPropertyWithDoc
{
    /**
     * @var mixed
     */
    private $notTitles;

    public function getTitle()
    {
        $this->titles = null;
        $titleCount = count($this->titles);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class LocalPropertyWithDoc
{
    /**
     * @var mixed
     */
    private $notTitles;

    public function getTitle()
    {
        $this->titles = null;
        $titleCount = $this->titles === null ? 0 : count($this->titles);
    }
}

?>
