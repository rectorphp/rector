<?php

namespace Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Fixture;

class KeepComment
{
    public function foo($foo)
    {
        //thiscommentshouldnotberemoved
        return $foo;
    }

    public function bar($bar)
    {
        // @var string $bar['should_not_be_changed]

        return $bar['should_not_be_changed'];
    }
}
