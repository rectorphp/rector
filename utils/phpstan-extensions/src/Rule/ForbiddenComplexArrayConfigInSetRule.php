<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;

/**
 * @todo make part of core symplify
 * @see \Rector\PHPStanExtensions\Tests\Rule\ForbiddenComplexArrayConfigInSetRule\ForbiddenComplexArrayConfigInSetRuleTest
 */
final class ForbiddenComplexArrayConfigInSetRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'For complex configuration use value object over array';

    public function getNodeType(): string
    {
        return ArrayItem::class;
    }

    /**
     * @param ArrayItem $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // typical for configuration
        if (! $node->key instanceof ClassConstFetch) {
            return [];
        }

        if (! $this->isInSymfonyPhpConfigClosure($scope)) {
            return [];
        }

        // simple → skip
        if (! $node->value instanceof Array_) {
            return [];
        }

        $valueArray = $node->value;
        foreach ($valueArray->items as $nestedItem) {
            if (! $nestedItem instanceof ArrayItem) {
                continue;
            }

            // way too complex
            if ($nestedItem->value instanceof Array_) {
                return [self::ERROR_MESSAGE];
            }
        }

        return [];
    }

    private function isInSymfonyPhpConfigClosure(Scope $scope): bool
    {
        // we are in a closure
        if ($scope->getAnonymousFunctionReflection() === null) {
            return false;
        }

        if (count($scope->getAnonymousFunctionReflection()->getParameters()) !== 1) {
            return false;
        }

        /** @var NativeParameterReflection $onlyParameter */
        $onlyParameter = $scope->getAnonymousFunctionReflection()->getParameters()[0];
        $onlyParameterType = $onlyParameter->getType();

        $containerConfiguratorObjectType = new ObjectType(
            'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator'
        );

        return $onlyParameterType->isSuperTypeOf($containerConfiguratorObjectType)->yes();
    }
}
