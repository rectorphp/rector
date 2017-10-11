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

    /**
     * @var string[]|Node[]
     */
    private $deprecations;

    /**
     * @deprecated use addDeprecation() instead
     */
    public function addDeprecationAnnotation(string $annotation, Node $node): void
    {
        $this->deprecationAnnotations[] = [
            'message' => $annotation,
            'node' => $node,
        ];
    }

    /**
     * @deprecated use addDeprecation() instead
     */
    public function addDeprecationTriggerError(Arg $argNode): void
    {
        $this->deprecationTriggerErrors[] = $argNode;
    }

    /**
     * @deprecated use getDeprecations() instead
     * @return string[]|Node[]
     */
    public function getDeprecationAnnotations(): array
    {
        return $this->deprecationAnnotations;
    }

    /**
     * @deprecated use getDeprecations() instead
     * @return Arg[]
     */
    public function getDeprecationTriggerErrors(): array
    {
        return $this->deprecationTriggerErrors;
    }

    public function addDeprecation(string $message, Node $node): void
    {
        $this->deprecations[] = [
            'message' => $message,
            'node' => $node
        ];
    }

    /**
     * @return mixed[]
     */
    public function getDeprecations(): array
    {
        return $this->deprecations;
    }
}
