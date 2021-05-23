<?php
declare(strict_types=1);


namespace Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source;


final class Generator
{
    public $word = '';

    public function word()
    {
        return 'foo';
    }
}
