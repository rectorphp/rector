<?php

declare(strict_types=1);

namespace Rector\NodeContainer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class FunctionLikeParsedNodesFinder
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function findClassMethodByMethodCall(MethodCall $methodCall): ?ClassMethod
    {
        /** @var string|null $className */
        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        $methodName = $this->nameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }

        return $this->parsedNodesByType->findMethod($methodName, $className);
    }

    public function findClassMethodByStaticCall(StaticCall $staticCall): ?ClassMethod
    {
        $methodName = $this->nameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }

        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);

        $classNames = TypeUtils::getDirectClassNames($objectType);
        foreach ($classNames as $className) {
            $foundMethod = $this->parsedNodesByType->findMethod($methodName, $className);
            if ($foundMethod !== null) {
                return $foundMethod;
            }
        }

        return null;
    }
}
