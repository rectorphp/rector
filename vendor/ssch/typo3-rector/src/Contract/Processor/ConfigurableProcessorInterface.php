<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\Processor;

use RectorPrefix20220606\Rector\Core\Contract\Processor\FileProcessorInterface;
interface ConfigurableProcessorInterface extends FileProcessorInterface
{
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void;
}
