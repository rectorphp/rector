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
    private const ERROR_NAMESPACE_OUTSIDE_TEST_DIR = '"Tests" namespace (%s) used outside of "tests" directory (%s)';

    /**
     * @var string
     */
    private const ERROR_TEST_FILE_OUTSIDE_NAMESPACE = 'Test file (%s) is outside of "Tests" namespace (%s)';

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
            if ($this->hasTestSuffix($scope)) {
                $errorMessage = sprintf(
                    self::ERROR_TEST_FILE_OUTSIDE_NAMESPACE,
                    $scope->getFileDescription(),
                    $node->name->toString()
                );
                return [$errorMessage];
            }

            return [];
        }

        if ($this->inTestsDirectory($scope)) {
            return [];
        }

        $errorMessage = sprintf(
            self::ERROR_NAMESPACE_OUTSIDE_TEST_DIR,
            $node->name->toString(),
            $scope->getFileDescription()
        );

        return [$errorMessage];
    }

    private function hasTestsNamespace(Name $name): bool
    {
        return in_array('Tests', $name->parts, true);
    }

    private function hasTestSuffix(Scope $scope): bool
    {
        return strstr($scope->getFileDescription(), 'Test.php') !== false;
    }

    private function inTestsDirectory(Scope $scope): bool
    {
        return strstr($scope->getFileDescription(), '/tests/') !== false;
    }
}
