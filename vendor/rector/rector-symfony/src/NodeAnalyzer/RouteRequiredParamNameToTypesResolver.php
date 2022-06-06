<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Attribute;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\Symfony\Enum\SymfonyAnnotation;
final class RouteRequiredParamNameToTypesResolver
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttrinationFinder
     */
    private $attrinationFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(AttrinationFinder $attrinationFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->attrinationFinder = $attrinationFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return array<string, Type>
     */
    public function resolve(ClassMethod $classMethod) : array
    {
        if ($classMethod->getParams() === []) {
            return [];
        }
        $routeAttrination = $this->attrinationFinder->getByOne($classMethod, SymfonyAnnotation::ROUTE);
        $paramsToRegexes = $this->resolveParamsToRegexes($routeAttrination);
        if ($paramsToRegexes === []) {
            return [];
        }
        $paramsToTypes = [];
        foreach ($paramsToRegexes as $paramName => $paramRegex) {
            if ($paramRegex === '\\d+') {
                $paramsToTypes[$paramName] = new IntegerType();
            }
            // @todo add for string/bool as well
        }
        return $paramsToTypes;
    }
    /**
     * @return array<string, string>
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute|null $routeAttrination
     */
    private function resolveParamsToRegexes($routeAttrination) : array
    {
        if ($routeAttrination instanceof DoctrineAnnotationTagValueNode) {
            return $this->resolveFromAnnotation($routeAttrination);
        }
        if ($routeAttrination instanceof Attribute) {
            return $this->resolveFromAttribute($routeAttrination);
        }
        return [];
    }
    /**
     * @return array<string, string>
     */
    private function resolveFromAnnotation(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : array
    {
        $paramsToRegexes = [];
        $requirementsValue = $doctrineAnnotationTagValueNode->getValue('requirements');
        if (!$requirementsValue instanceof CurlyListNode) {
            return [];
        }
        foreach ($requirementsValue->getValuesWithExplicitSilentAndWithoutQuotes() as $paramName => $paramRegex) {
            if (!\is_string($paramName)) {
                continue;
            }
            $paramsToRegexes[$paramName] = (string) $paramRegex;
        }
        return $paramsToRegexes;
    }
    /**
     * @return array<string, string>
     */
    private function resolveFromAttribute(Attribute $attribute) : array
    {
        $paramsToRegexes = [];
        foreach ($attribute->args as $arg) {
            if (!$this->nodeNameResolver->isName($arg, 'requirements')) {
                continue;
            }
            $requirementsArray = $arg->value;
            if (!$requirementsArray instanceof Array_) {
                continue;
            }
            foreach ($requirementsArray->items as $arrayItem) {
                if (!$arrayItem instanceof ArrayItem) {
                    continue;
                }
                $arrayKey = $arrayItem->key;
                if (!$arrayKey instanceof String_) {
                    continue;
                }
                $paramName = $arrayKey->value;
                $arrayValue = $arrayItem->value;
                if (!$arrayValue instanceof String_) {
                    continue;
                }
                $paramType = $arrayValue->value;
                $paramsToRegexes[$paramName] = $paramType;
            }
        }
        return $paramsToRegexes;
    }
}
