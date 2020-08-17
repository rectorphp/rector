<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipScalars
{
    private $string;
    private $float;
    private $int;

    public function __construct(string $string, float $float, int $int)
    {
        $this->string = $string;
        $this->float = $float;
        $this->int = $int;
    }

    public function go()
    {
        if ($this->string) {
            return 'yes';
        }

        if ($this->float) {
            return 'yes';
        }

        if ($this->int) {
            return 'yes';
        }
    }
}
