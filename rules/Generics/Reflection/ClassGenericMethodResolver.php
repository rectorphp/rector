<?php

declare(strict_types=1);

namespace Rector\Generics\Reflection;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\Reflection\MethodReflection;
use Rector\Generics\TagValueNodeFactory\MethodTagValueNodeFactory;
use Rector\Generics\ValueObject\ChildParentClassReflections;
use Symplify\SimplePhpDocParser\SimplePhpDocParser;
use Symplify\SimplePhpDocParser\ValueObject\Ast\PhpDoc\SimplePhpDocNode;

final class ClassGenericMethodResolver
{
    /**
     * @var SimplePhpDocParser
     */
    private $simplePhpDocParser;

    /**
     * @var MethodTagValueNodeFactory
     */
    private $methodTagValueNodeFactory;

    public function __construct(
        SimplePhpDocParser $simplePhpDocParser,
        MethodTagValueNodeFactory $methodTagValueNodeFactory
    ) {
        $this->simplePhpDocParser = $simplePhpDocParser;
        $this->methodTagValueNodeFactory = $methodTagValueNodeFactory;
    }

    /**
     * @return MethodTagValueNode[]
     */
    public function resolveFromClass(ChildParentClassReflections $genericChildParentClassReflections): array
    {
        $methodTagValueNodes = [];

        $classReflection = $genericChildParentClassReflections->getParentClassReflection();
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
                $methodReflection,
                $genericChildParentClassReflections
            );
            if (! $methodTagValueNode instanceof MethodTagValueNode) {
                continue;
            }

            $methodTagValueNodes[] = $methodTagValueNode;
        }

        return $methodTagValueNodes;
    }

    /**
     * @param string[] $templateNames
     */
    private function resolveMethodTagValueNode(
        SimplePhpDocNode $simplePhpDocNode,
        array $templateNames,
        MethodReflection $methodReflection,
        ChildParentClassReflections $genericChildParentClassReflections
    ): ?MethodTagValueNode {
        foreach ($simplePhpDocNode->getReturnTagValues() as $returnTagValueNode) {
            foreach ($templateNames as $templateName) {
                $typeAsString = (string) $returnTagValueNode->type;
                if (! Strings::match($typeAsString, '#\b' . preg_quote($templateName, '#') . '\b#')) {
                    continue;
                }

                return $this->methodTagValueNodeFactory->createFromMethodReflectionAndReturnTagValueNode(
                    $methodReflection,
                    $returnTagValueNode,
                    $genericChildParentClassReflections
                );
            }
        }

        return null;
    }
}
