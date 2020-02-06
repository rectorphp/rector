<?php

declare(strict_types=1);

namespace Rector\Core\Extension;

use Rector\Core\Contract\Extension\FinishingExtensionInterface;

final class FinishingExtensionRunner
{
    /**
     * @var FinishingExtensionInterface[]
     */
    private $finishingExtensions = [];

    /**
     * @param FinishingExtensionInterface[] $finishingExtensions
     */
    public function __construct(array $finishingExtensions = [])
    {
        $this->finishingExtensions = $finishingExtensions;
    }

    public function run(): void
    {
        foreach ($this->finishingExtensions as $finishingExtension) {
            $finishingExtension->run();
        }
    }
}
