<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class HeaderControl
{
    private $titles = [];

    /**
     * @var mixed[]
     */
    private $alsoTitles;

    /**
     * @var mixed
     */
    private $notTitles;

    public function getTitle()
    {
        count($this->titles);

        $this->titles = null;
        $titleCount = count($this->titles);

        $alsoTitlesCount = count($this->alsoTitles);

        $notTitlesCount = count($this->notTitles);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class HeaderControl
{
    private $titles = [];

    /**
     * @var mixed[]
     */
    private $alsoTitles;

    /**
     * @var mixed
     */
    private $notTitles;

    public function getTitle()
    {
        count($this->titles);

        $this->titles = null;
        $titleCount = $this->titles === null ? 0 : count($this->titles);

        $alsoTitlesCount = count($this->alsoTitles);

        $notTitlesCount = is_array($this->notTitles) || $this->notTitles instanceof \Countable ? count($this->notTitles) : 0;
    }
}

?>
