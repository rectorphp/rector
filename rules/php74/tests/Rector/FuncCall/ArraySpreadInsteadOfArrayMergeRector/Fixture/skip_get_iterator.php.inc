<?php

namespace Rector\Php74\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector\Fixture;

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
