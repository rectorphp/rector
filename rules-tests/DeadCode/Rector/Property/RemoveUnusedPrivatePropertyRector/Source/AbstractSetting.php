<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Source;

abstract class AbstractSetting
{
	public function __construct(PageInterface $page, SectionInterface $section)
    {
        // do something with $page and $section
        $page->runSomething();
        $section->runSomething();
    }
}
