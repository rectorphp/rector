<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
interface FormYamlRectorInterface extends RectorInterface
{
    /**
     * @param mixed[] $yaml
     * @return mixed[]
     */
    public function refactor(array $yaml) : array;
}
