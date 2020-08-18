<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use Symplify\Statie\Configuration\StatieConfiguration;

final class SkipNotYetUsed
{
    /**
     * @var string|null
     */
    private $detectedTemplating;

    public function __construct(StatieConfiguration $statieConfiguration)
    {
        $this->statieConfiguration = $statieConfiguration;
    }

    public function detect(): string
    {
        if ($this->detectedTemplating) {
            return $this->detectedTemplating;
        }

        $twigFileCount = 1;
        $latteFileCount = 2;

        $this->detectedTemplating = $twigFileCount > $latteFileCount ? 'twig' : 'latte';

        return $this->detectedTemplating;
    }
}
