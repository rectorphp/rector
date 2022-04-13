<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\NodeNameResolver\NodeNameResolver;
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
    public function __construct(\Rector\Doctrine\NodeAnalyzer\AttrinationFinder $attrinationFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->attrinationFinder = $attrinationFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return array<string, Type>
     */
    public function resolve(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        if ($classMethod->getParams() === []) {
            return [];
        }
        $routeAttrination = $this->attrinationFinder->getByOne($classMethod, 'Symfony\\Component\\Routing\\Annotation\\Route');
        $paramsToRegexes = $this->resolveParamsToRegexes($routeAttrination);
        if ($paramsToRegexes === []) {
            return [];
        }
        $paramsToTypes = [];
        foreach ($paramsToRegexes as $paramName => $paramRegex) {
            if ($paramRegex === '\\d+') {
                $paramsToTypes[$paramName] = new \PHPStan\Type\IntegerType();
            }
            // @todo add for string/bool as well
        }
        return $paramsToTypes;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node\Attribute|\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|null $routeAttrination
     */
    private function resolveParamsToRegexes($routeAttrination) : array
    {
        if ($routeAttrination instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return $this->resolveFromAnnotation($routeAttrination);
        }
        if ($routeAttrination instanceof \PhpParser\Node\Attribute) {
            return $this->resolveFromAttribute($routeAttrination);
        }
        return [];
    }
    /**
     * @return array<string, string>
     */
    private function resolveFromAnnotation(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : array
    {
        $paramsToRegexes = [];
        $requirementsValue = $doctrineAnnotationTagValueNode->getValue('requirements');
        if (!$requirementsValue instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
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
    private function resolveFromAttribute(\PhpParser\Node\Attribute $attribute) : array
    {
        $paramsToRegexes = [];
        foreach ($attribute->args as $arg) {
            if (!$this->nodeNameResolver->isName($arg, 'requirements')) {
                continue;
            }
            $requirementsArray = $arg->value;
            if (!$requirementsArray instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($requirementsArray->items as $arrayItem) {
                if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                    continue;
                }
                $arrayKey = $arrayItem->key;
                if (!$arrayKey instanceof \PhpParser\Node\Scalar\String_) {
                    continue;
                }
                $paramName = $arrayKey->value;
                $arrayValue = $arrayItem->value;
                if (!$arrayValue instanceof \PhpParser\Node\Scalar\String_) {
                    continue;
                }
                $paramType = $arrayValue->value;
                $paramsToRegexes[$paramName] = $paramType;
            }
        }
        return $paramsToRegexes;
    }
}
