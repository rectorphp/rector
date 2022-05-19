<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipNoDefaultReturnFluent
{
    private ?string $result = null;

    public function run($value)
    {
        switch (strtolower($value)) {
            case 'a':
                $this->result = 0;
                break;
            case 'b':
                $this->result = 1;
                break;
        }

        return $this;
    }
}
