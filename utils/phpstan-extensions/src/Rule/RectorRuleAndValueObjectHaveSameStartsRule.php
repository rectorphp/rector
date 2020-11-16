<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\NodeAnalyzer\SymfonyConfigRectorValueObjectResolver;
use Rector\PHPStanExtensions\NodeAnalyzer\TypeAndNameAnalyzer;
use Symplify\PHPStanRules\Naming\SimpleNameResolver;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\RectorRuleAndValueObjectHaveSameStartsRuleTest
 */
final class RectorRuleAndValueObjectHaveSameStartsRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Change "%s" name to "%s", so it respects the Rector rule name';

    /**
     * @var string
     * @see https://regex101.com/r/Fk6iou/1
     */
    private const RECTOR_SUFFIX_REGEX = '#Rector$#';

    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;

    /**
     * @var TypeAndNameAnalyzer
     */
    private $typeAndNameAnalyzer;

    /**
     * @var SymfonyConfigRectorValueObjectResolver
     */
    private $symfonyConfigRectorValueObjectResolver;

    public function __construct(
        SimpleNameResolver $simpleNameResolver,
        TypeAndNameAnalyzer $typeAndNameAnalyzer,
        SymfonyConfigRectorValueObjectResolver $symfonyConfigRectorValueObjectResolver
    ) {
        $this->simpleNameResolver = $simpleNameResolver;
        $this->typeAndNameAnalyzer = $typeAndNameAnalyzer;
        $this->symfonyConfigRectorValueObjectResolver = $symfonyConfigRectorValueObjectResolver;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->shouldSkip($node, $scope)) {
            return [];
        }

        $rectorShortClass = $this->resolveRectorShortClass($node);
        if ($rectorShortClass === null) {
            return [];
        }

        $valueObjectShortClass = $this->resolveValueObjectShortClass($node);
        if ($valueObjectShortClass === null) {
            return [];
        }

        $expectedValueObjectShortClass = Strings::replace($rectorShortClass, self::RECTOR_SUFFIX_REGEX, '');

        if ($expectedValueObjectShortClass === $valueObjectShortClass) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $valueObjectShortClass, $expectedValueObjectShortClass);
        return [$errorMessage];
    }

    private function shouldSkip(MethodCall $methodCall, Scope $scope): bool
    {
        return ! $this->typeAndNameAnalyzer->isMethodCallTypeAndName(
            $methodCall,
            $scope,
            'Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator', 'set'
        );
    }

    private function resolveShortClass(string $class): string
    {
        return (string) Strings::after($class, '\\', -1);
    }

    private function resolveRectorShortClass(MethodCall $methodCall): ?string
    {
        $setFirstArgValue = $methodCall->args[0]->value;
        if (! $setFirstArgValue instanceof ClassConstFetch) {
            return null;
        }

        $rectorClass = $this->simpleNameResolver->getName($setFirstArgValue->class);
        if ($rectorClass === null) {
            return null;
        }

        return $this->resolveShortClass($rectorClass);
    }

    private function resolveValueObjectShortClass(MethodCall $methodCall): ?string
    {
        $valueObjectClass = $this->symfonyConfigRectorValueObjectResolver->resolveFromSetMethodCall($methodCall);
        if ($valueObjectClass === null) {
            return null;
        }

        // is it implements interface, it can have many forms
        if (class_implements($valueObjectClass) !== []) {
            return null;
        }

        return $this->resolveShortClass($valueObjectClass);
    }
}
