<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObject;

use Rector\ChangesReporting\Annotation\AnnotationExtractor;
use Rector\Core\Contract\Rector\RectorInterface;

final class RectorWithFileAndLineChange
{
    /**
     * @var string
     */
    private $realPath;

    /**
     * @var int
     */
    private $line;

    /**
     * @var RectorInterface
     */
    private $rector;

    public function __construct(RectorInterface $rector, string $realPath, int $line)
    {
        $this->rector = $rector;
        $this->line = $line;
        $this->realPath = $realPath;
    }

    public function getRectorDefinitionsDescription(): string
    {
        $ruleDefinition = $this->rector->getRuleDefinition();
        return $ruleDefinition->getDescription();
    }

    public function getRectorClass(): string
    {
        return get_class($this->rector);
    }

    public function getRectorClassWithChangelogUrl(AnnotationExtractor $annotationExtractor): string
    {
        $rectorClass = get_class($this->rector);
        $changeLogUrl = $this->getChangelogUrl($annotationExtractor);

        if ($changeLogUrl === null) {
            return $rectorClass;
        }

        return sprintf('%s (%s)', $rectorClass, $changeLogUrl);
    }

    public function getChangelogUrl(AnnotationExtractor $annotationExtractor): ?string
    {
        $rectorClass = get_class($this->rector);

        return $annotationExtractor->extractAnnotationFromClass($rectorClass, '@changelog');
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }
}
