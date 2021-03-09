<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
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

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        RenamedClassesCollector $renamedClassesCollector,
        ReflectionProvider $reflectionProvider
    ) {
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
<<<<<<< HEAD
     * @return class-string<Node>
=======
     * @return class-string<\PhpParser\Node>
>>>>>>> 1b83ff428... add return type class string
     */
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

        if (in_array($name, ['static', 'self'], true)) {
            return $this->createClassReferenceType($node, $name);
        }

        return $this->createScalarType($name);
    }

    private function isExistingClass(string $name): bool
    {
        if ($this->reflectionProvider->hasClass($name)) {
            return true;
        }

        // to be existing class names
        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClasses();

        return in_array($name, $oldToNewClasses, true);
    }

    private function createClassReferenceType(Name $name, string $reference): Type
    {
        $className = $name->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return new MixedType();
        }

        if ($reference === 'static') {
            return new StaticType($className);
        }

        if ($this->reflectionProvider->hasClass($className)) {
            $classReflection = $this->reflectionProvider->getClass($className);
            return new ThisType($classReflection);
        }

        return new ThisType($className);
    }

    private function createScalarType(string $name): Type
    {
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
}
