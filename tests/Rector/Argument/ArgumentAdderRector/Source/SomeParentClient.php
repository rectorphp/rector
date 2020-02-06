<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Source;

class SomeParentClient
{
    public function submit(\DomCrawlerForm $form, array $values = [])
    {
    }
}
