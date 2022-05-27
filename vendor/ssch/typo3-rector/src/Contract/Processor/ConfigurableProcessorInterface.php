<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\Processor;

use Rector\Core\Contract\Processor\FileProcessorInterface;
interface ConfigurableProcessorInterface extends FileProcessorInterface
{
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void;
}
