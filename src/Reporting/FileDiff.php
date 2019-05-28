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
     * @var string
     */
    private $diffConsoleFormatted;

    /**
     * @param string[] $appliedRectorClasses
     */
    public function __construct(
        string $file,
        string $diff,
        string $diffConsoleFormatted,
        array $appliedRectorClasses = []
    ) {
        $this->file = $file;
        $this->diff = $diff;
        $this->appliedRectorClasses = $appliedRectorClasses;
        $this->diffConsoleFormatted = $diffConsoleFormatted;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function getDiffConsoleFormatted(): string
    {
        return $this->diffConsoleFormatted;
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
