<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\PropertyRenamer\MatchTypePropertyRenamer;
use Rector\Naming\PropertyRenamer\PropertyFetchRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    private bool $hasChanged = false;

    public function __construct(
        private MatchTypePropertyRenamer $matchTypePropertyRenamer,
        private PropertyRenameFactory $propertyRenameFactory,
        private MatchPropertyTypeExpectedNameResolver $matchPropertyTypeExpectedNameResolver,
        private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver,
        private PropertyFetchRenamer $propertyFetchRenamer,
        private ParamRenameFactory $paramRenameFactory,
        private ParamRenamer $paramRenamer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename property and method param to match its type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $eventManager;

    public function __construct(EntityManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Interface_::class];
    }

    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->refactorClassProperties($node);
        $this->renamePropertyPromotion($node);

        if (! $this->hasChanged) {
            return null;
        }

        return $node;
    }

    private function refactorClassProperties(ClassLike $classLike): void
    {
        foreach ($classLike->getProperties() as $property) {
            $expectedPropertyName = $this->matchPropertyTypeExpectedNameResolver->resolve($property);
            if ($expectedPropertyName === null) {
                continue;
            }

            $propertyRename = $this->propertyRenameFactory->createFromExpectedName($property, $expectedPropertyName);
            if (! $propertyRename instanceof PropertyRename) {
                continue;
            }

            $renameProperty = $this->matchTypePropertyRenamer->rename($propertyRename);
            if (! $renameProperty instanceof Property) {
                continue;
            }

            $this->hasChanged = true;
        }
    }

    private function renamePropertyPromotion(ClassLike $classLike): void
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            return;
        }

        $constructClassMethod = $classLike->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        $desiredPropertyNames = [];
        foreach ($constructClassMethod->params as $key => $param) {
            if ($param->flags === 0) {
                continue;
            }

            // promoted property
            $desiredPropertyName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($desiredPropertyName === null) {
                continue;
            }

            if (in_array($desiredPropertyName, $desiredPropertyNames, true)) {
                return;
            }

            $desiredPropertyNames[$key] = $desiredPropertyName;
        }

        $this->renameParamVarName($classLike, $constructClassMethod, $desiredPropertyNames);
    }

    /**
     * @param string[] $desiredPropertyNames
     */
    private function renameParamVarName(
        ClassLike $classLike,
        ClassMethod $constructClassMethod,
        array $desiredPropertyNames
    ): void {
        $keys = array_keys($desiredPropertyNames);
        $params = $constructClassMethod->params;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($constructClassMethod);

        foreach ($params as $key => $param) {
            if (in_array($key, $keys, true)) {
                $currentName = $this->getName($param);
                $desiredPropertyName = $desiredPropertyNames[$key];
                $this->propertyFetchRenamer->renamePropertyFetchesInClass(
                    $classLike,
                    $currentName,
                    $desiredPropertyName
                );

                /** @var string $paramVarName */
                $paramVarName = $param->var->name;
                $this->renameParamDoc($phpDocInfo, $param, $paramVarName, $desiredPropertyName);
                $param->var->name = $desiredPropertyName;
            }
        }
    }

    private function renameParamDoc(
        PhpDocInfo $phpDocInfo,
        Param $param,
        string $paramVarName,
        string $desiredPropertyName
    ): void
    {
        $paramTagValueNode = $phpDocInfo->getParamTagValueNodeByName($paramVarName);

        if (! $paramTagValueNode instanceof ParamTagValueNode) {
            return;
        }

        $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($param, $desiredPropertyName);
        if (! $paramRename instanceof ParamRename) {
            return;
        }

        $this->paramRenamer->rename($paramRename);
    }
}
