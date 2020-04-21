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
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\TypeDeclaration\PhpParserTypeAnalyzer;
use Rector\VendorLocker\VendorLockResolver;

/**
 * @see https://wiki.php.net/rfc/scalar_type_hints_v5
 * @see https://github.com/nikic/TypeUtil
 * @see https://github.com/nette/type-fixer
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 */
abstract class AbstractTypeDeclarationRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @var ParsedNodeCollector
     */
    protected $parsedNodeCollector;

    /**
     * @var PhpParserTypeAnalyzer
     */
    protected $phpParserTypeAnalyzer;

    /**
     * @var VendorLockResolver
     */
    protected $vendorLockResolver;

    /**
     * @required
     */
    public function autowireAbstractTypeDeclarationRector(
        DocBlockManipulator $docBlockManipulator,
        ParsedNodeCollector $parsedNodeCollector,
        PhpParserTypeAnalyzer $phpParserTypeAnalyzer,
        VendorLockResolver $vendorLockResolver
    ): void {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->phpParserTypeAnalyzer = $phpParserTypeAnalyzer;
        $this->vendorLockResolver = $vendorLockResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @return Name|NullableType|Identifier|UnionType|null
     */
    protected function resolveChildTypeNode(Type $type): ?Node
    {
        if ($type instanceof MixedType) {
            return null;
        }

        if ($type instanceof SelfObjectType || $type instanceof StaticType) {
            $type = new ObjectType($type->getClassName());
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);
    }
}
