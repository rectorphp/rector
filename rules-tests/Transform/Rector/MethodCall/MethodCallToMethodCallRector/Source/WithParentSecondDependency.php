<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToMethodCallRector\Source;

use Nette\DI\Attributes\Inject;

abstract class WithParentSecondDependency
{
    #[Inject]
    public SecondDependency $secondDependency;
}
