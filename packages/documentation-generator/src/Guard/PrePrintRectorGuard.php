<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Guard;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;

final class PrePrintRectorGuard
{
    public function ensureRectorRefinitionHasContent(RectorInterface $rector): void
    {
        $this->ensureRectorDefinitionExists($rector);
        $this->ensureCodeSampleExists($rector);
    }

    private function ensureRectorDefinitionExists(RectorInterface $rector): void
    {
        $rectorDefinition = $rector->getDefinition();
        if ($rectorDefinition->getDescription() !== '') {
            return;
        }

        $message = sprintf(
            'Rector "%s" is missing description. Complete it in "%s()" method.',
            get_class($rector),
            'getDefinition'
        );
        throw new ShouldNotHappenException($message);
    }

    private function ensureCodeSampleExists(RectorInterface $rector): void
    {
        $rectorDefinition = $rector->getDefinition();
        if (count($rectorDefinition->getCodeSamples()) !== 0) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Rector "%s" must have at least one code sample. Complete it in "%s()" method.',
            get_class($rector),
            'getDefinition'
        ));
    }
}
