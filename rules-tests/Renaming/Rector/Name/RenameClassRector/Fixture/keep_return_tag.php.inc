<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use Manual\Twig\TwigFilter;

final class KeepReturnTag
{
    /**
     * @return TwigFilter
     */
    public function keep()
    {
    }
}
