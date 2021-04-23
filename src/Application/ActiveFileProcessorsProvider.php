<?php
declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Symplify\Skipper\Skipper\Skipper;

final class ActiveFileProcessorsProvider
{
    /**
     * @var FileProcessorInterface[]
     */
    private $fileProcessors = [];

    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(Skipper $skipper, array $fileProcessors)
    {
        $this->fileProcessors = array_filter($fileProcessors, function (FileProcessorInterface $fileProcessor) use (
            $skipper
        ) {
            return ! $skipper->shouldSkipElement($fileProcessor);
        });
    }

    /**
     * @return FileProcessorInterface[]
     */
    public function provide(): array
    {
        return $this->fileProcessors;
    }
}
