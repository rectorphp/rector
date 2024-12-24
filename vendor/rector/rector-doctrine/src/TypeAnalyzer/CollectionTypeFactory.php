<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypeAnalyzer;

use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
final class CollectionTypeFactory
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, AstResolver $astResolver, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->astResolver = $astResolver;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function createType(ObjectType $objectType, bool $withIndexBy, $property) : GenericObjectType
    {
        $keyType = new IntegerType();
        if ($withIndexBy) {
            $keyType = $this->resolveKeyType($property, $objectType->getClassName());
        }
        $genericTypes = [$keyType, $objectType];
        return new GenericObjectType('Doctrine\\Common\\Collections\\Collection', $genericTypes);
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     * @return \PHPStan\Type\IntegerType|\PHPStan\Type\StringType
     */
    private function resolveKeyType($property, string $className)
    {
        $class = $this->astResolver->resolveClassFromName($className);
        if (!$class instanceof Class_) {
            return new IntegerType();
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        // key need to be initialized here
        // so it can be checked in target class annotation or attribute
        $key = null;
        if ($phpDocInfo instanceof PhpDocInfo) {
            // only on OneToMany and ManyToMany
            // https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/tutorials/working-with-indexed-associations.html#mapping-indexed-associations
            $annotations = $phpDocInfo->findByAnnotationClass('Doctrine\\ORM\\Mapping\\OneToMany') !== [] ? $phpDocInfo->findByAnnotationClass('Doctrine\\ORM\\Mapping\\OneToMany') : $phpDocInfo->findByAnnotationClass('Doctrine\\ORM\\Mapping\\ManyToMany');
            if (\count($annotations) === 1 && $annotations[0] instanceof DoctrineAnnotationTagValueNode) {
                foreach ($annotations[0]->getValues() as $arrayItemNode) {
                    if ($arrayItemNode instanceof ArrayItemNode && $arrayItemNode->key instanceof StringNode && $arrayItemNode->key->value === 'indexBy' && $arrayItemNode->value instanceof StringNode) {
                        $key = $arrayItemNode->value->value;
                        break;
                    }
                }
                if ($key !== null) {
                    $type = $this->resolveKeyFromAnnotation($class, $key);
                    if ($type instanceof Type) {
                        return $type;
                    }
                }
            }
        }
        $attrGroups = $property->attrGroups;
        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if (\in_array($attr->name->toString(), ['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany'], \true)) {
                    foreach ($attr->args as $arg) {
                        if ($arg->name instanceof Identifier && $arg->name->name === 'indexBy' && $arg->value instanceof String_) {
                            $key = $arg->value->value;
                            $type = $this->resolveKeyFromAnnotation($class, $key);
                            if ($type instanceof Type) {
                                return $type;
                            }
                            break;
                        }
                    }
                }
            }
        }
        if ($key !== null) {
            return $this->resolveKeyFromAttribute($class, $key);
        }
        return new IntegerType();
    }
    /**
     * @return null|\PHPStan\Type\IntegerType|\PHPStan\Type\StringType
     */
    private function resolveKeyFromAnnotation(Class_ $class, string $key)
    {
        // get property from class
        $targetProperty = $class->getProperty($key);
        if (!$targetProperty instanceof Property) {
            return new IntegerType();
        }
        $phpDocInfoTargetClass = $this->phpDocInfoFactory->createFromNode($targetProperty);
        if ($phpDocInfoTargetClass instanceof PhpDocInfo) {
            $columns = $phpDocInfoTargetClass->findByAnnotationClass('Doctrine\\ORM\\Mapping\\Column');
            if (\count($columns) === 1 && $columns[0] instanceof DoctrineAnnotationTagValueNode) {
                $type = null;
                foreach ($columns[0]->getValues() as $arrayItemNode) {
                    if ($arrayItemNode instanceof ArrayItemNode && $arrayItemNode->key === 'type' && $arrayItemNode->value instanceof StringNode) {
                        $type = $arrayItemNode->value->value;
                        break;
                    }
                }
                return $type === null ? new IntegerType() : ($type === 'string' ? new StringType() : new IntegerType());
            }
        }
        return null;
    }
    /**
     * @return \PHPStan\Type\IntegerType|\PHPStan\Type\StringType
     */
    private function resolveKeyFromAttribute(Class_ $class, string $key)
    {
        // get property from class
        $targetProperty = $class->getProperty($key);
        if (!$targetProperty instanceof Property) {
            return new IntegerType();
        }
        $attrGroups = $targetProperty->attrGroups;
        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === 'Doctrine\\ORM\\Mapping\\Column') {
                    foreach ($attr->args as $arg) {
                        if ($arg->name instanceof Identifier && $arg->name->name === 'type') {
                            $type = $this->valueResolver->getValue($arg->value);
                            return $type === 'string' ? new StringType() : new IntegerType();
                        }
                    }
                }
            }
        }
        return new IntegerType();
    }
}
