<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeValue\NodeValueResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetAttributeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var array<string, string|string[]>>
     */
    private const ARGUMENT_KEY_TO_RETURN_TYPE = [
        AttributeKey::class . '::FILE_INFO' => SmartFileInfo::class,
        AttributeKey::class . '::RESOLVED_NAME' => Name::class,
        AttributeKey::class . '::CLASS_NODE' => ClassLike::class,
        AttributeKey::class . '::METHOD_NODE' => ClassMethod::class,
        AttributeKey::class . '::CURRENT_EXPRESSION' => Stmt::class,
        AttributeKey::class . '::PREVIOUS_STATEMENT' => Stmt::class,
        AttributeKey::class . '::SCOPE' => Scope::class,
        # Node
        AttributeKey::class . '::ORIGINAL_NODE' => Node::class,
        AttributeKey::class . '::PARENT_NODE' => Node::class,
        AttributeKey::class . '::NEXT_NODE' => Node::class,
        AttributeKey::class . '::PREVIOUS_NODE' => Node::class,
        AttributeKey::class . '::USE_NODES' => [Use_::class],
        # scalars
        AttributeKey::class . '::CLASS_NAME' => 'string',
    ];

    /**
     * @var NodeValueResolver
     */
    private $nodeValueResolver;

    public function __construct(NodeValueResolver $nodeValueResolver)
    {
        $this->nodeValueResolver = $nodeValueResolver;
    }

    public function getClass(): string
    {
        return Node::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getAttribute';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

        $argumentValue = $this->resolveArgumentValue($methodCall->args[0]->value, $scope);
        if ($argumentValue === null) {
            return $returnType;
        }

        if (! isset(self::ARGUMENT_KEY_TO_RETURN_TYPE[$argumentValue])) {
            return $returnType;
        }

        $knownReturnType = self::ARGUMENT_KEY_TO_RETURN_TYPE[$argumentValue];
        if ($knownReturnType === 'string') {
            return new UnionType([new StringType(), new NullType()]);
        }

        if (is_array($knownReturnType) && count($knownReturnType) === 1) {
            $arrayType = new ArrayType(new IntegerType(), new ObjectType($knownReturnType[0]));
            return new UnionType([$arrayType, new NullType()]);
        }

        if (is_string($knownReturnType)) {
            return new UnionType([new ObjectType($knownReturnType), new NullType()]);
        }

        return $returnType;
    }

    private function resolveArgumentValue(Expr $expr, Scope $scope): ?string
    {
        if ($expr instanceof ClassConstFetch) {
            return $this->nodeValueResolver->resolve($expr, $scope->getFile());
        }

        return null;
    }
}
