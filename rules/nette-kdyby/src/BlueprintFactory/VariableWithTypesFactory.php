<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\BlueprintFactory;

use PhpParser\Node\Arg;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NetteKdyby\ValueObject\VariableWithType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class VariableWithTypesFactory
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        VariableNaming $variableNaming,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper,
        ClassNaming $classNaming
    ) {
        $this->variableNaming = $variableNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classNaming = $classNaming;
    }

    /**
     * @param Arg[] $args
     * @return VariableWithType[]
     */
    public function createVariablesWithTypesFromArgs(array $args): array
    {
        $variablesWithTypes = [];

        foreach ($args as $arg) {
            $variableName = $this->variableNaming->resolveFromNode($arg);
            $staticType = $this->nodeTypeResolver->getStaticType($arg->value);

            // compensate for static
            if ($staticType instanceof StaticType) {
                $staticType = new ObjectType($staticType->getClassName());
            }

            if ($variableName === 'this') {
                $shortClassName = $this->classNaming->getShortName($staticType->getClassName());
                $variableName = lcfirst($shortClassName);
            }

            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($staticType);

            $variablesWithTypes[] = new VariableWithType($variableName, $staticType, $phpParserTypeNode);
        }

        return $variablesWithTypes;
    }
}
