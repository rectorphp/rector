<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

class SkipUsedInTrait
{
    public function go()
    {
    }
}

trait SomeTrait
{
    /**
     * @var SkipUsedInTrait
     */
    private $skipUsedInTrait;

    public function run()
    {
        $this->skipUsedInTrait->go();
    }
}

class AnotherClassUsingTheTraitJustToMakePHPStanWork
{
    use SomeTrait;
}
