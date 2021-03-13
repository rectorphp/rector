<?php

namespace Rector\Tests\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

use Symfony\Component\Finder\Finder;

class SkipGetIreator
{
    public function run()
    {
        $finder = Finder::create()->files()
            ->in(__DIR__ . '/Source');

        $files = iterator_to_array($finder->getIterator());
    }
}
