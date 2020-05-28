<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\BlueprintFactory;

use PhpParser\Node\Arg;
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
            $variableName = $this->variableNaming->resolveFromNode($arg);
            $staticType = $this->nodeTypeResolver->getStaticType($arg->value);
            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($staticType);

            $variablesWithTypes[] = new VariableWithType($variableName, $staticType, $phpParserTypeNode);
        }

        return $variablesWithTypes;
    }
}
