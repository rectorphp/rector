<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\FalseBooleanType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class NameNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    public function __construct(RenamedClassesCollector $renamedClassesCollector)
    {
        $this->renamedClassesCollector = $renamedClassesCollector;
    }

    public function getNodeType(): string
    {
        return Name::class;
    }

    /**
     * @param Name $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $name = $node->toString();

        if ($this->isExistingClass($name)) {
            return new FullyQualifiedObjectType($name);
        }

        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return new MixedType();
        }

        if ($name === 'static') {
            return new StaticType($className);
        }

        if ($name === 'self') {
            return new ThisType($className);
        }

        if ($name === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if ($name === 'int') {
            return new IntegerType();
        }

        if ($name === 'float') {
            return new FloatType();
        }

        if ($name === 'string') {
            return new StringType();
        }

        if ($name === 'false') {
            return new FalseBooleanType();
        }

        if ($name === 'bool') {
            return new BooleanType();
        }

        return new MixedType();
    }

    private function isExistingClass(string $name): bool
    {
        if (ClassExistenceStaticHelper::doesClassLikeExist($name)) {
            return true;
        }

        // to be existing class names
        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClasses();

        return in_array($name, $oldToNewClasses, true);
    }
}
