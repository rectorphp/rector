<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use PhpParser\Node;

/**
 * Collected from "deprecated" annotations and
 * from trigger_error(*, E_USER_DEPRECATED) function calls
 */
final class DeprecationCollector
{
    /**
     * @var string[]|Node[]
     */
    private $deprecations = [];

    public function addDeprecation(string $message, Node $node): void
    {
        $this->deprecations[] = [
            'message' => $message,
            'node' => $node,
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
