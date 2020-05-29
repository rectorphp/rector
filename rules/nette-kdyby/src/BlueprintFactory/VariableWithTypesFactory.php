<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\BlueprintFactory;

use PhpParser\Node\Arg;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
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

    public function __construct(
        VariableNaming $variableNaming,
        NodeTypeResolver $nodeTypeResolver,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->variableNaming = $variableNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    /**
     * @param Arg[] $args
     * @return VariableWithType[]
     */
    public function createVariablesWithTypesFromArgs(array $args): array
    {
        $variablesWithTypes = [];

        foreach ($args as $arg) {
            $staticType = $this->nodeTypeResolver->getStaticType($arg->value);
            $variableName = $this->variableNaming->resolveFromNode($arg, $staticType);

            // compensate for static
            if ($staticType instanceof StaticType) {
                $staticType = new ObjectType($staticType->getClassName());
            }

            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($staticType);

            $variablesWithTypes[] = new VariableWithType($variableName, $staticType, $phpParserTypeNode);
        }

        return $variablesWithTypes;
    }
}
