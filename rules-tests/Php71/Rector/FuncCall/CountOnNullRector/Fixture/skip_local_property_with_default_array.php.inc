<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipLocalPropertyWithDefaultArray
{
    private $titles = [];

    /**
     * @var mixed[]
     */
    private $alsoTitles = [];

    public function getTitle()
    {
        $this->titles = null;
        $titleCount = count($this->titles);

        $alsoTitlesCount = count($this->alsoTitles);
    }
}
