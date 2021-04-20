<?php

use Rector\Core\Exception\ShouldNotHappenException;

final class SkipException
{
    public function getValues()
    {
        throw new ShouldNotHappenException();
    }
}
