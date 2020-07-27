<?php

namespace Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Fixture;

class KeepDoubleComments
{
    /*****************************************
     * should not be removed
     ****************************************/
    /**
     * Some multi line
     * dock block
     */
    public function bizz()
    {
        return null;
    }
}
