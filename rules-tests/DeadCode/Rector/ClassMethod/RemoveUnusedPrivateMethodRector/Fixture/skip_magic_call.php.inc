<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

/**
 * @see https://3v4l.org/Lh5O5
 */
class SkipMagicCall
{
    private function test($params)
    {

    }

    public function __call($name, $params)
    {
        echo $name;
        var_dump($params);
    }

    public function execute()
    {
        $this->__call('test', []);
    }
}
