<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\Rector\AbstractRector;

/**
 * @see https://wiki.php.net/rfc/scalar_type_hints_v5
 * @see https://github.com/nikic/TypeUtil
 * @see https://github.com/nette/type-fixer
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 */
abstract class AbstractTypeDeclarationRector extends AbstractRector
{
    /**
     * @var string
     */
    public const HAS_NEW_INHERITED_TYPE = 'has_new_inherited_return_type';

    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @var ParsedNodesByType
     */
    protected $parsedNodesByType;

    /**
     * @required
     */
    public function autowireAbstractTypeDeclarationRector(
        DocBlockManipulator $docBlockManipulator,
        ParsedNodesByType $parsedNodesByType
    ): void {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->parsedNodesByType = $parsedNodesByType;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @param Name|NullableType|UnionType|Identifier $possibleSubtype
     * @param Name|NullableType|UnionType|Identifier $type
     */
    protected function isSubtypeOf(Node $possibleSubtype, Node $type): bool
    {
        // skip until PHP 8 is out
        if ($possibleSubtype instanceof UnionType || $type instanceof UnionType) {
            return false;
        }

        // union types are not covariant
        if ($possibleSubtype instanceof NullableType && ! $type instanceof NullableType) {
            return false;
        }

        if (! $possibleSubtype instanceof NullableType && $type instanceof NullableType) {
            return false;
        }

        // unwrap nullable types
        if ($type instanceof NullableType) {
            $type = $type->type;
            /** @var NullableType $possibleSubtype */
            $possibleSubtype = $possibleSubtype->type;
        }

        $possibleSubtype = $possibleSubtype->toString();
        $type = $type->toString();
        if (is_a($possibleSubtype, $type, true)) {
            return true;
        }

        if (in_array($possibleSubtype, ['array', 'Traversable'], true) && $type === 'iterable') {
            return true;
        }

        if (in_array($possibleSubtype, ['array', 'ArrayIterator'], true) && $type === 'countable') {
            return true;
        }

        if ($type === $possibleSubtype) {
            return true;
        }

        return ctype_upper($possibleSubtype[0]) && $type === 'object';
    }

    /**
     * @return Name|NullableType|Identifier|UnionType|null
     */
    protected function resolveChildTypeNode(Type $type): ?Node
    {
        if ($type instanceof MixedType) {
            return null;
        }

        if ($type instanceof SelfObjectType) {
            $type = new ObjectType($type->getClassName());
        } elseif ($type instanceof StaticType) {
            $type = new ObjectType($type->getClassName());
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);
    }
}
