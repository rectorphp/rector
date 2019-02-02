<?php declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rector\Type;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
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
use Rector\PHPStanExtensions\Utils\PHPStanValueResolver;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class GetAttributeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var string[]|string[][]
     */
    private $argumentKeyToReturnType = [
        'Rector\NodeTypeResolver\Node\Attribute::FILE_INFO' => SmartFileInfo::class,
        'Rector\NodeTypeResolver\Node\Attribute::RESOLVED_NAME' => Name::class,
        'Rector\NodeTypeResolver\Node\Attribute::CLASS_NODE' => ClassLike::class,
        'Rector\NodeTypeResolver\Node\Attribute::METHOD_NODE' => ClassMethod::class,
        'Rector\NodeTypeResolver\Node\Attribute::CURRENT_EXPRESSION' => Expression::class,
        'Rector\NodeTypeResolver\Node\Attribute::PREVIOUS_EXPRESSION' => Expression::class,
        'Rector\NodeTypeResolver\Node\Attribute::SCOPE' => Scope::class,
        # Node
        'Rector\NodeTypeResolver\Node\Attribute::ORIGINAL_NODE' => Node::class,
        'Rector\NodeTypeResolver\Node\Attribute::PARENT_NODE' => Node::class,
        'Rector\NodeTypeResolver\Node\Attribute::NEXT_NODE' => Node::class,
        'Rector\NodeTypeResolver\Node\Attribute::PREVIOUS_NODE' => Node::class,
        'Rector\NodeTypeResolver\Node\Attribute::USE_NODES' => [Use_::class],
        # scalars
        'Rector\NodeTypeResolver\Node\Attribute::PARENT_CLASS_NAME' => 'string',
        'Rector\NodeTypeResolver\Node\Attribute::NAMESPACE_NAME' => 'string',
        'Rector\NodeTypeResolver\Node\Attribute::CLASS_NAME' => 'string',
        'Rector\NodeTypeResolver\Node\Attribute::METHOD_NAME' => 'string',
    ];

    /**
     * @var PHPStanValueResolver
     */
    private $phpStanValueResolver;

    public function __construct(PHPStanValueResolver $phpStanValueResolver)
    {
        $this->phpStanValueResolver = $phpStanValueResolver;
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

        $argumentValue = $this->resolveArgumentValue($methodCall->args[0]->value);
        if ($argumentValue === null) {
            return $returnType;
        }

        if (! isset($this->argumentKeyToReturnType[$argumentValue])) {
            return $returnType;
        }

        $knownReturnType = $this->argumentKeyToReturnType[$argumentValue];
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

    private function resolveArgumentValue(Expr $node): ?string
    {
        if ($node instanceof ClassConstFetch) {
            return $this->phpStanValueResolver->resolveClassConstFetch($node);
        }

        return null;
    }
}
