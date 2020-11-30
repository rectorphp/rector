<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\ConfigurableRectorRule\ConfigurableRectorRuleTest
 */
final class ConfigurableRectorRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_NO_CONFIGURED_CODE_SAMPLE = 'Configurable rules must have configure code sample';

    /**
     * @var string
     */
    public const ERROR_NOT_IMPLEMENTS_INTERFACE = 'Configurable code sample is used but "%s" interface is not implemented';

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->hasRectorInClassName($node)) {
            return [];
        }

        if ($node->isAbstract()) {
            return [];
        }

        if (! $this->implementsConfigurableInterface($node)) {
            if ($this->hasConfiguredCodeSample($node)) {
                $errorMessage = sprintf(self::ERROR_NOT_IMPLEMENTS_INTERFACE, ConfigurableRectorInterface::class);
                return [$errorMessage];
            }

            return [];
        }

        if ($this->hasConfiguredCodeSample($node)) {
            return [];
        }

        return [self::ERROR_NO_CONFIGURED_CODE_SAMPLE];
    }

    private function hasRectorInClassName(Class_ $class): bool
    {
        if (! property_exists($class, 'namespacedName') || $class->namespacedName === null) {
            return false;
        }

        return Strings::endsWith((string) $class->namespacedName, 'Rector');
    }

    private function implementsConfigurableInterface(Class_ $class): bool
    {
        if (! property_exists($class, 'namespacedName') || $class->namespacedName === null) {
            return false;
        }

        $fullyQualifiedClassName = (string) $class->namespacedName;
        return is_a($fullyQualifiedClassName, ConfigurableRectorInterface::class, true);
    }

    private function hasConfiguredCodeSample(Class_ $class): bool
    {
        $classMethod = $class->getMethod('getRuleDefinition');
        if ($classMethod === null) {
            return false;
        }

        if ($classMethod->stmts === null) {
            return false;
        }

        $nodeFinder = new NodeFinder();
        $nodes = $nodeFinder->find($classMethod->stmts, function (Node $node): ?New_ {
            if (! $node instanceof New_) {
                return null;
            }

            $className = $node->class;
            if (! $className instanceof Name) {
                return null;
            }

            if (is_a($className->toString(), ConfiguredCodeSample::class, true)) {
                return $node;
            }

            return null;
        });

        return $nodes !== [];
    }
}
