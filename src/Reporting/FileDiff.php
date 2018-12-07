<?php declare(strict_types=1);

namespace Rector\Reporting;

final class FileDiff
{
    /**
     * @var string
     */
    private $diff;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string[]
     */
    private $appliedRectorClasses = [];

    /**
     * @param string[] $appliedRectorClasses
     */
    public function __construct(string $file, string $diff, array $appliedRectorClasses = [])
    {
        $this->file = $file;
        $this->diff = $diff;
        $this->appliedRectorClasses = $appliedRectorClasses;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string[]
     */
    public function getAppliedRectorClasses(): array
    {
        return $this->appliedRectorClasses;
    }
}
