<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ConfigurationNodeFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        NodeFactory $nodeFactory,
        ParameterProvider $parameterProvider,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->parameterProvider = $parameterProvider;
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     * @return Property[]
     */
    public function createProperties(array $ruleConfiguration): array
    {
        $this->lowerPhpVersion();

        $properties = [];
        foreach (array_keys($ruleConfiguration) as $constantName) {
            $propertyName = StaticRectorStrings::uppercaseUnderscoreToCamelCase($constantName);
            $type = new ArrayType(new MixedType(), new MixedType());

            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $type);
            $property->props[0]->default = new Array_([]);
            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     * @return ClassConst[]
     */
    public function createConfigurationConstants(array $ruleConfiguration): array
    {
        $classConsts = [];

        foreach (array_keys($ruleConfiguration) as $constantName) {
            $constantName = strtoupper($constantName);
            $constantValue = strtolower($constantName);
            $classConst = $this->nodeFactory->createPublicClassConst($constantName, $constantValue);
            $classConsts[] = $classConst;
        }

        return $classConsts;
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    public function createConfigureClassMethod(array $ruleConfiguration): ClassMethod
    {
        $this->lowerPhpVersion();

        $classMethod = $this->nodeFactory->createPublicMethod('configure');
        $classMethod->returnType = new Identifier('void');

        $configurationVariable = new Variable('configuration');
        $configurationParam = new Param($configurationVariable);
        $configurationParam->type = new Identifier('array');
        $classMethod->params[] = $configurationParam;

        $assigns = [];
        foreach (array_keys($ruleConfiguration) as $constantName) {
            $coalesce = $this->createConstantInConfigurationCoalesce($constantName, $configurationVariable);

            $propertyName = StaticRectorStrings::uppercaseUnderscoreToCamelCase($constantName);
            $assign = $this->nodeFactory->createPropertyAssignmentWithExpr($propertyName, $coalesce);
            $assigns[] = new Expression($assign);
        }

        $classMethod->stmts = $assigns;

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $identifierTypeNode = new IdentifierTypeNode('mixed[]');
        $paramTagValueNode = new ParamTagValueNode($identifierTypeNode, false, '$configuration', '');
        $phpDocInfo->addTagValueNode($paramTagValueNode);

        return $classMethod;
    }

    /**
     * So types are PHP 7.2 compatible
     */
    private function lowerPhpVersion(): void
    {
        $this->parameterProvider->changeParameter(
            Option::PHP_VERSION_FEATURES,
            PhpVersionFeature::TYPED_PROPERTIES - 1
        );
    }

    private function createConstantInConfigurationCoalesce(
        string $constantName,
        Variable $configurationVariable
    ): Coalesce {
        $constantName = strtoupper($constantName);

        $classConstFetch = new ClassConstFetch(new Name('self'), $constantName);
        $arrayDimFetch = new ArrayDimFetch($configurationVariable, $classConstFetch);

        $emptyArray = new Array_([]);

        return new Coalesce($arrayDimFetch, $emptyArray);
    }
}
