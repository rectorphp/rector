<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\Enum\SymfonyAnnotation;
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
            if (\in_array($paramRegex, ['\\d+', '\\d'], \true)) {
                $paramsToTypes[$paramName] = new IntegerType();
                continue;
            }
            if ($paramRegex === '\\w+') {
                $paramsToTypes[$paramName] = new StringType();
                continue;
            }
            // fallback to string or improve later
            $paramsToTypes[$paramName] = new StringType();
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
        $requirementsArrayItemNode = $doctrineAnnotationTagValueNode->getValue('requirements');
        if (!$requirementsArrayItemNode instanceof ArrayItemNode) {
            return [];
        }
        if (!$requirementsArrayItemNode->value instanceof CurlyListNode) {
            return [];
        }
        foreach ($requirementsArrayItemNode->value->getValuesWithSilentKey() as $nestedArrayItemNode) {
            $paramRegex = $nestedArrayItemNode->value;
            if ($paramRegex instanceof StringNode) {
                $paramRegex = $paramRegex->value;
            }
            if (!\is_string($paramRegex)) {
                continue;
            }
            $paramName = $nestedArrayItemNode->key;
            if ($paramName instanceof StringNode) {
                $paramName = $paramName->value;
            }
            if (!\is_string($paramName)) {
                continue;
            }
            $paramsToRegexes[$paramName] = $paramRegex;
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
