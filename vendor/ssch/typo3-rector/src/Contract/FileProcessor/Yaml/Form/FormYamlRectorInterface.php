<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form;

use Rector\Core\Contract\Rector\RectorInterface;
interface FormYamlRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @param mixed[] $yaml
     * @return mixed[]
     */
    public function refactor(array $yaml) : array;
}
