<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\AddParamTypeDeclarationRectorTest
 */
final class AddParamTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PARAMETER_TYPEHINTS = 'parameter_typehintgs';

    /**
     * @var AddParamTypeDeclaration[]
     */
    private $parameterTypehints = [];

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(TypeComparator $typeComparator)
    {
        $this->typeComparator = $typeComparator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $configuration = [
            self::PARAMETER_TYPEHINTS => [new AddParamTypeDeclaration('SomeClass', 'process', 0, new StringType())],
        ];

        return new RuleDefinition('Add param types where needed', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function process($name)
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function process(string $name)
    {
    }
}
CODE_SAMPLE
            ,
                $configuration
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var ClassLike $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        foreach ($this->parameterTypehints as $parameterTypehint) {
            if (! $this->isObjectType($classLike, $parameterTypehint->getClassName())) {
                continue;
            }

            if (! $this->isName($node, $parameterTypehint->getMethodName())) {
                continue;
            }

            $this->refactorClassMethodWithTypehintByParameterPosition($node, $parameterTypehint);
        }

        return $node;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $parameterTypehints = $configuration[self::PARAMETER_TYPEHINTS] ?? [];
        Assert::allIsInstanceOf($parameterTypehints, AddParamTypeDeclaration::class);
        $this->parameterTypehints = $parameterTypehints;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        // skip class methods without args
        if ((array) $classMethod->params === []) {
            return true;
        }

        /** @var ClassLike|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        // skip traits
        if ($classLike instanceof Trait_) {
            return true;
        }

        // skip class without parents/interfaces
        if ($classLike instanceof Class_) {
            if ($classLike->implements !== []) {
                return false;
            }

            if ($classLike->extends !== null) {
                return false;
            }

            return true;
        }

        // skip interface without parents
        /** @var Interface_ $classLike */
        return ! (bool) $classLike->extends;
    }

    private function refactorClassMethodWithTypehintByParameterPosition(
        ClassMethod $classMethod,
        AddParamTypeDeclaration $addParamTypeDeclaration
    ): void {
        $parameter = $classMethod->params[$addParamTypeDeclaration->getPosition()] ?? null;
        if ($parameter === null) {
            return;
        }

        $this->refactorParameter($parameter, $addParamTypeDeclaration);
    }

    private function refactorParameter(Param $param, AddParamTypeDeclaration $addParamTypeDeclaration): void
    {
        // already set → no change
        if ($param->type !== null) {
            $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            if ($this->typeComparator->areTypesEqual($currentParamType, $addParamTypeDeclaration->getParamType())) {
                return;
            }
        }

        // remove it
        if ($addParamTypeDeclaration->getParamType() instanceof MixedType) {
            $param->type = null;
            return;
        }

        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $addParamTypeDeclaration->getParamType()
        );

        $param->type = $returnTypeNode;
    }
}
