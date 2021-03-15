<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\FixturePhp80;

use stdClass;

final class SkipPropertyPromotion
{
	public function __construct(private stdClass $demofile)
    {
    }

    public function getName() {
        return __CLASS__;
    }

    public function foo() {
        return $this->demofile->getName();
    }
}

?>
