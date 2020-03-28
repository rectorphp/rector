<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

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
