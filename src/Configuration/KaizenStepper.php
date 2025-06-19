<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Contract\Rector\RectorInterface;
final class KaizenStepper
{
    /**
     * @var positive-int|null
     */
    private ?int $stepCount = null;
    /**
     * @var array<class-string<RectorInterface>>
     */
    private array $appliedRectorClasses = [];
    /**
     * @param positive-int $stepCount
     */
    public function setStepCount(int $stepCount) : void
    {
        $this->stepCount = $stepCount;
    }
    public function enabled() : bool
    {
        return $this->stepCount !== null;
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function recordAppliedRule(string $rectorClass) : void
    {
        $this->appliedRectorClasses[] = $rectorClass;
    }
    public function shouldKeepImproving(string $rectorClass) : bool
    {
        // is rule already in applied rules? keep going
        $uniqueAppliedRectorClasses = \array_unique($this->appliedRectorClasses);
        if (\in_array($rectorClass, $uniqueAppliedRectorClasses)) {
            return \true;
        }
        // make sure we made enough changes
        return \count($uniqueAppliedRectorClasses) < $this->stepCount;
    }
}
