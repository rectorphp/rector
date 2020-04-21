<?php

declare(strict_types=1);

namespace Rector\Core\Contract\RectorDefinition;

interface CodeSampleInterface
{
    public function getCodeBefore(): string;

    public function getCodeAfter(): string;

    /**
     * Content of newly created file
     */
    public function getExtraFileContent(): ?string;
}
