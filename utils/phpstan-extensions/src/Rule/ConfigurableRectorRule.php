<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\PHPStanRules\Naming\SimpleNameResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\ConfigurableRectorRule\ConfigurableRectorRuleTest
 */
final class ConfigurableRectorRule implements Rule
{
    /**
     * @todo implement in symplify + add test :)
     * @var string
     */
    public const ERROR_NO_CONFIGURED_CODE_SAMPLE = 'Configurable rules must have configure code sample';

    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;

    public function __construct(SimpleNameResolver $simpleNameResolver)
    {
        $this->simpleNameResolver = $simpleNameResolver;
    }

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
        $className = $this->simpleNameResolver->getName($node);
        if ($className === null) {
            return [];
        }

        if (! is_a($className, RectorInterface::class, true)) {
            return [];
        }

        if ($node->isAbstract()) {
            return [];
        }

        if ($this->hasConfigurableInterface($className)) {
            if ($this->hasConfiguredCodeSample($node)) {
                return [];
            }

            return [self::ERROR_NO_CONFIGURED_CODE_SAMPLE];
        }

        if ($this->hasConfiguredCodeSample($node)) {
            if ($this->hasConfigurableInterface($className)) {
                return [];
            }

            $errorMessage = sprintf(self::ERROR_NOT_IMPLEMENTS_INTERFACE, ConfigurableRectorInterface::class);
            return [$errorMessage];
        }

        return [];
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

    private function hasConfigurableInterface(string $className): bool
    {
        return is_a($className, ConfigurableRectorInterface::class, true);
    }
}
