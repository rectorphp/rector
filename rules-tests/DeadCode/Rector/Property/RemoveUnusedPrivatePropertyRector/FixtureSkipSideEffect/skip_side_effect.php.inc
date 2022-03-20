<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\FixtureSkipSideEffect;

use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class SkipSideEffect extends \Rector\Testing\PHPUnit\AbstractTestCase
{
    private $someService;

    protected function setUp(): void
    {
        $this->someService = $this->getService('Some');
    }
}
