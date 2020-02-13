<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule\ClassLike;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

final class KeepRectorNamespaceForRectorRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->shouldSkip($node, $scope)) {
            return [];
        }

        /** @var string $classLikeName */
        $classLikeName = $node->name->toString();

        $ruleError = $this->createRuleError($node, $scope, $classLikeName);

        return [$ruleError];
    }

    private function shouldSkip(Node $node, Scope $scope): bool
    {
        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return true;
        }

        // skip interface and tests
        if (Strings::match($namespace, '#\\\\(Contract|Exception|Tests)\\\\#')) {
            return true;
        }

        if (! Strings::endsWith($namespace, 'Rector') && ! Strings::match($namespace, '#\\\\Rector\\\\#')) {
            return true;
        }

        $name = $node->name;
        if ($name === null) {
            return true;
        }

        // correct name
        $classLikeName = $name->toString();

        return (bool) Strings::match($classLikeName, '#(Rector|Test|Trait)$#');
    }

    private function createRuleError(Node $node, Scope $scope, string $classLikeName): RuleError
    {
        $message = sprintf(
            'Change namespace for "%s". It cannot be in "Rector" namespace, unless Rector rule.',
            $classLikeName
        );

        $ruleErrorBuilder = RuleErrorBuilder::message($message);
        $ruleErrorBuilder->line($node->getLine());
        $ruleErrorBuilder->file($scope->getFile());

        return $ruleErrorBuilder->build();
    }
}
