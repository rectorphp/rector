<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\CheckNotTestsNamespaceOutsideTestsDirectoryRule\CheckNotTestsNamespaceOutsideTestsDirectoryRuleTest
 */
final class CheckNotTestsNamespaceOutsideTestsDirectoryRule implements Rule
{
    /**
     * @var string
     */
    private const ERROR_MESSAGE = '"Tests" namespace (%s) used outside of "tests" directory (%s)';

    public function getNodeType(): string
    {
        return Namespace_::class;
    }

    /**
     * @param Namespace_ $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->name === null) {
            return [];
        }

        if (! $this->hasTestsNamespace($node->name)) {
            return [];
        }

        if ($this->inTestsDirectory($scope)) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $node->name->toString(), $scope->getFileDescription());
        return [$errorMessage];
    }

    private function hasTestsNamespace(Name $name): bool
    {
        return in_array('Tests', $name->parts, true);
    }

    private function inTestsDirectory(Scope $scope): bool
    {
        return strstr($scope->getFileDescription(), '/tests/') !== false;
    }
}
