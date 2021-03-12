<?php

declare(strict_types=1);

namespace Rector\Arguments\Tests\Rector\ClassMethod\ArgumentAdderRector\Source;

class SomeParentClient
{
    public function submit(\DomCrawlerForm $form, array $values = [])
    {
    }
}
