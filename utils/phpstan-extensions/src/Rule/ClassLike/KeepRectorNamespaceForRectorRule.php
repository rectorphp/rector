<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule\ClassLike;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\ClassLike\KeepRectorNamespaceForRectorRuleTest
 */
final class KeepRectorNamespaceForRectorRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Change namespace for "%s". It cannot be in "Rector" namespace, unless Rector rule.';

    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->shouldSkip($node, $scope)) {
            return [];
        }

        /** @var string $classLikeName */
        $classLikeName = $node->name->toString();

        $errorMessage = sprintf(self::ERROR_MESSAGE, $classLikeName);

        return [$errorMessage];
    }

    private function shouldSkip(ClassLike $classLike, Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return true;
        }

        // skip interface and tests, except tests here
        if (Strings::match($namespace, '#\\\\(Contract|Exception|Tests)\\\\#') && ! Strings::contains(
            $namespace,
            'PHPStanExtensions'
        )) {
            return true;
        }

        if (! Strings::endsWith($namespace, '\\Rector') && ! Strings::match($namespace, '#\\\\Rector\\\\#')) {
            return true;
        }

        $name = $classLike->name;
        if ($name === null) {
            return true;
        }

        // correct name
        $classLikeName = $name->toString();

        return (bool) Strings::match($classLikeName, '#(Rector|Test|Trait)$#');
    }
}
