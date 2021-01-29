<?php

declare(strict_types=1);

namespace Rector\Generics\Reflection;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\MixedType;
use Rector\Generics\TagValueNodeFactory\MethodTagValueParameterNodeFactory;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\SimplePhpDocParser\SimplePhpDocParser;
use Symplify\SimplePhpDocParser\ValueObject\Ast\PhpDoc\SimplePhpDocNode;

final class ClassGenericMethodResolver
{
    /**
     * @var SimplePhpDocParser
     */
    private $simplePhpDocParser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var MethodTagValueParameterNodeFactory
     */
    private $methodTagValueParameterNodeFactory;

    public function __construct(
        SimplePhpDocParser $simplePhpDocParser,
        StaticTypeMapper $staticTypeMapper,
        MethodTagValueParameterNodeFactory $methodTagValueParameterNodeFactory
    ) {
        $this->simplePhpDocParser = $simplePhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->methodTagValueParameterNodeFactory = $methodTagValueParameterNodeFactory;
    }

    /**
     * @return MethodTagValueNode[]
     */
    public function resolveFromClass(ClassReflection $classReflection): array
    {
        $methodTagValueNodes = [];

        $templateNames = array_keys($classReflection->getTemplateTags());

        foreach ($classReflection->getNativeMethods() as $methodReflection) {
            $parentMethodDocComment = $methodReflection->getDocComment();
            if ($parentMethodDocComment === null) {
                continue;
            }

            // how to parse?
            $parentMethodSimplePhpDocNode = $this->simplePhpDocParser->parseDocBlock($parentMethodDocComment);
            $methodTagValueNode = $this->resolveMethodTagValueNode(
                $parentMethodSimplePhpDocNode,
                $templateNames,
                $methodReflection
            );
            if ($methodTagValueNode === null) {
                continue;
            }

            $methodTagValueNodes[] = $methodTagValueNode;
        }

        return $methodTagValueNodes;
    }

    /**
     * @param ParameterReflection[] $parameterReflections
     * @return MethodTagValueParameterNode[]
     */
    private function resolveStringParameters(array $parameterReflections): array
    {
        $stringParameters = [];

        foreach ($parameterReflections as $parameterReflection) {
            $stringParameters[] = $this->methodTagValueParameterNodeFactory->createFromParamReflection($parameterReflection);
        }

        return $stringParameters;
    }



    /**
     * @param string[] $templateNames
     */
    private function resolveMethodTagValueNode(
        SimplePhpDocNode $simplePhpDocNode,
        array $templateNames,
        MethodReflection $methodReflection
    ): ?MethodTagValueNode {
        foreach ($simplePhpDocNode->getReturnTagValues() as $returnTagValueNode) {
            foreach ($templateNames as $templateName) {
                $typeAsString = (string) $returnTagValueNode->type;
                if (! Strings::match($typeAsString, '#\b' . preg_quote($templateName, '#') . '\b#')) {
                    continue;
                }

                // @todo resolve params etc
                $parameterReflections = $methodReflection->getVariants()[0]
                    ->getParameters();

                $stringParameters = $this->resolveStringParameters($parameterReflections);

                return new MethodTagValueNode(
                    false,
                    $returnTagValueNode->type,
                    $methodReflection->getName(),
                    $stringParameters,
                    ''
                );
            }
        }

        return null;
    }
}
