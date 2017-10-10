<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use PhpParser\Node;
use PhpParser\Node\Arg;

final class DeprecationCollector
{
    /**
     * Collected from "deprecated" annotations
     *
     * @var string[]|Node[]
     */
    private $deprecationAnnotations = [];

    /**
     * Collected from trigger_error(*, E_USER_DEPRECATED) function calls
     *
     * @var Arg[]
     */
    private $deprecationTriggerErrors = [];

    public function addDeprecationAnnotation(string $annotation, Node $node): void
    {
        $this->deprecationAnnotations[] = [
            'message' => $annotation,
            'node' => $node,
        ];
    }

    public function addDeprecationTriggerError(Arg $argNode): void
    {
        $this->deprecationTriggerErrors[] = $argNode;
    }

    /**
     * @return string[]|Node[]
     */
    public function getDeprecationAnnotations(): array
    {
        return $this->deprecationAnnotations;
    }

    /**
     * @return Arg[]
     */
    public function getDeprecationTriggerErrors(): array
    {
        return $this->deprecationTriggerErrors;
    }
}
