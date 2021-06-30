# How To Work with Doc Block and Comments

Let's say we have a doc block:

```php
/**
 * @return int
 */
public function run()
{
    return 1000;
}
```

## How to get a Return Type?

To get e.g. return type, use `PhpDocInfo` value object with useful methods:

```php
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;

/** @var PhpDocInfoFactory $phpDocInfoFactory */
$phpDocInfo = $phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

// then use any method you like
$returnType = $phpDocInfo->getReturnType();
// instance of "\PHPStan\Type\IntegerType"
var_dump($returnType);
```

## How to Remove node?

```php
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

/** @var PhpDocInfo $phpDocInfo */
$phpDocInfo->removeByType(ReturnTagValueNode::class);
```

## How create PhpDocInfo for a new node?

In case you build a new node and want to work with its doc block, you need to create it first:

```php
// the "PhpDocInfoFactory" service is already available in children of "AbstractRector"
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;

/** @var PhpDocInfoFactory $phpDocInfoFactory */
$phpDocInfo = $phpDocInfoFactory->createFromNodeOrEmpty($node);
```

## How to get Param with Names and Types?

```php
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

/** @var PhpDocInfo $phpDocInfo */
$paramTypes = $phpDocInfo->getParamTypesByName();

/** @var array<string, Type> $paramTypes */
var_dump($paramTypes);
```

## How to Get Class Annotation?

Doctrine class annotations are annotations based on [`doctrine/annotations`](https://github.com/doctrine/annotations/) package. They are classes that have `@annotation`. Most common are Doctrine entity, column, one to many etc., but also Symfony route or Symfony validation annotations.

Let's look how to work one for Doctrine entity:

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserEntity
{
}
```

```php
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

/** @var PhpDocInfo $phpDocInfo */
$entityTagValueNode = $phpDocInfo->findOneByAnnotationClass('Doctrine\ORM\Mapping\Entity');
if (! $entityTagValueNode instanceof DoctrineAnnotationTagValueNode) {
    return null;
}

$annotationClass = $entityTagValueNode->identifierTypeNode;
var_dump($annotationClass); // \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode("Doctrine\ORM\Mapping\Entity")

$values = $entityTagValueNode->getValues();
var_dump($values); // []
```
