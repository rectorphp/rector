<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

final class SkipUsedInTrait
{
    public $go;
}

trait SomeTrait
{
    /**
     * @var SkipUsedInTrait
     */
    private $skipUsedInTrait;

    public function run()
    {
        $this->skipUsedInTrait->go;
    }
}

class AnotherClassUsingTheTraitJustToMakePHPStanWork
{
    use SomeTrait;
}
