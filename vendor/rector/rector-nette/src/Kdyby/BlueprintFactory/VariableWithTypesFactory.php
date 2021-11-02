<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\BlueprintFactory;

use PhpParser\Node\Arg;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Naming\Naming\VariableNaming;
use Rector\Nette\Kdyby\ValueObject\VariableWithType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class VariableWithTypesFactory
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->variableNaming = $variableNaming;
    }
    /**
     * @param Arg[] $args
     * @return VariableWithType[]
     */
    public function createVariablesWithTypesFromArgs(array $args) : array
    {
        $variablesWithTypes = [];
        foreach ($args as $arg) {
            $staticType = $this->nodeTypeResolver->getType($arg->value);
            $variableName = $this->variableNaming->resolveFromNodeAndType($arg, $staticType);
            if ($variableName === null) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            // compensate for static
            if ($staticType instanceof \PHPStan\Type\StaticType) {
                $staticType = new \PHPStan\Type\ObjectType($staticType->getClassName());
            }
            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($staticType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
            $variablesWithTypes[] = new \Rector\Nette\Kdyby\ValueObject\VariableWithType($variableName, $staticType, $phpParserTypeNode);
        }
        return $variablesWithTypes;
    }
}
