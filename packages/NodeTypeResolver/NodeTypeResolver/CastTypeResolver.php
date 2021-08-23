<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\String_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
final class CastTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_TYPE_MAP = [\PhpParser\Node\Expr\Cast\Bool_::class => \PHPStan\Type\BooleanType::class, \PhpParser\Node\Expr\Cast\String_::class => \PHPStan\Type\StringType::class, \PhpParser\Node\Expr\Cast\Int_::class => \PHPStan\Type\IntegerType::class, \PhpParser\Node\Expr\Cast\Double::class => \PHPStan\Type\FloatType::class];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Expr\Cast::class];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type
    {
        foreach (self::CAST_CLASS_TO_TYPE_MAP as $castClass => $typeClass) {
            if (\is_a($node, $castClass, \true)) {
                return new $typeClass();
            }
        }
        if ($node instanceof \PhpParser\Node\Expr\Cast\Array_) {
            return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        }
        if ($node instanceof \PhpParser\Node\Expr\Cast\Object_) {
            return new \PHPStan\Type\ObjectType('stdClass');
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(\get_class($node));
    }
}
