<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class SkipReferenced
{
    public function run()
    {
        if (empty($rows)) {
            return;
        }

        if ($config['headers'] === true) {
            $this->_render(array_shift($rows), $widths, ['style' => $config['headerStyle']]);
        }

        if (empty($rows)) {
            return;
        }
    }
}
