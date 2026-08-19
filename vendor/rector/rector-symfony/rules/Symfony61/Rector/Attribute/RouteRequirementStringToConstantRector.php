<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers:
 * - https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#routing
 *
 * @see \Rector\Symfony\Tests\Symfony61\Rector\Attribute\RouteRequirementStringToConstantRector\RouteRequirementStringToConstantRectorTest
 */
final class RouteRequirementStringToConstantRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const REQUIREMENT_CLASS = 'Symfony\Component\Routing\Requirement\Requirement';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/routing', '>=6.1');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace regex string in #[Route] requirements with a Requirement constant', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Attribute\Route;

final class SomeController
{
    #[Route('/detail/{id}', requirements: [
        'id' => '[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}',
    ])]
    public function detail()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class SomeController
{
    #[Route('/detail/{id}', requirements: [
        'id' => Requirement::UUID_V4,
    ])]
    public function detail()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }
    /**
     * @param Attribute $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isNames($node->name, [SymfonyAttribute::ROUTE, SymfonyAnnotation::ROUTE])) {
            return null;
        }
        $requirementsArray = $this->resolveRequirementsArray($node);
        if (!$requirementsArray instanceof Array_) {
            return null;
        }
        $constantNamesByValue = $this->resolveConstantNamesByValue();
        if ($constantNamesByValue === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($requirementsArray->items as $arrayItem) {
            if (!$arrayItem->value instanceof String_) {
                continue;
            }
            $constantName = $constantNamesByValue[$arrayItem->value->value] ?? null;
            if ($constantName === null) {
                continue;
            }
            $arrayItem->value = $this->nodeFactory->createClassConstFetch(self::REQUIREMENT_CLASS, $constantName);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function resolveRequirementsArray(Attribute $attribute): ?Array_
    {
        foreach ($attribute->args as $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->isName($arg->name, 'requirements')) {
                continue;
            }
            if (!$arg->value instanceof Array_) {
                return null;
            }
            return $arg->value;
        }
        return null;
    }
    /**
     * @return array<string, string>
     */
    private function resolveConstantNamesByValue(): array
    {
        if (!$this->reflectionProvider->hasClass(self::REQUIREMENT_CLASS)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass(self::REQUIREMENT_CLASS);
        $constantNamesByValue = [];
        foreach ($classReflection->getNativeReflection()->getConstants() as $constantName => $constantValue) {
            // skip enum cases and non-regex constants
            if (!is_string($constantValue)) {
                continue;
            }
            $constantNamesByValue[$constantValue] = $constantName;
        }
        return $constantNamesByValue;
    }
}
