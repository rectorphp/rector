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
use Rector\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Cast>
 */
final class CastTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_TYPE_MAP = [Bool_::class => BooleanType::class, String_::class => StringType::class, Int_::class => IntegerType::class, Double::class => FloatType::class];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function resolve(Node $node) : Type
    {
        foreach (self::CAST_CLASS_TO_TYPE_MAP as $castClass => $typeClass) {
            if ($node instanceof $castClass) {
                return new $typeClass();
            }
        }
        if ($node instanceof Array_) {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($node instanceof Object_) {
            return new ObjectType('stdClass');
        }
        throw new NotImplementedYetException(\get_class($node));
    }
}
