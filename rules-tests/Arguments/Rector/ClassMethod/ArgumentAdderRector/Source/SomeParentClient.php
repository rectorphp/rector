<?php

declare(strict_types=1);

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source;

class SomeParentClient
{
    public function submit(\DomCrawlerForm $form, array $values = [])
    {
    }
}
