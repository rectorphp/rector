<?php declare(strict_types=1);

namespace Rector\Contract\RectorDefinition;

interface CodeSampleInterface
{
    public function getCodeBefore(): string;

    public function getCodeAfter(): string;
}
