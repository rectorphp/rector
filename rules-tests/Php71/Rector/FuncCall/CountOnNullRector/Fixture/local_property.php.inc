<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class LocalProperty
{
    private $titles = [];

    /**
     * @var mixed[]
     */
    private $alsoTitles = [];

    /**
     * @var mixed
     */
    private $notTitles;

    public function getTitle()
    {
        $this->titles = null;
        $titleCount = count($this->titles);

        $alsoTitlesCount = count($this->alsoTitles);

        $notTitlesCount = count($this->notTitles);
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class LocalProperty
{
    private $titles = [];

    /**
     * @var mixed[]
     */
    private $alsoTitles = [];

    /**
     * @var mixed
     */
    private $notTitles;

    public function getTitle()
    {
        $this->titles = null;
        $titleCount = $this->titles === null ? 0 : count($this->titles);

        $alsoTitlesCount = count($this->alsoTitles);

        $notTitlesCount = is_array($this->notTitles) || $this->notTitles instanceof \Countable ? count($this->notTitles) : 0;
    }
}

?>
