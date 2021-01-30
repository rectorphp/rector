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

Each class annotation has their own object to work with. E.g. to work with Doctrine entities:

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserEntity
{
}
```

You can use `EntityTagValueNode` object:

```php
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;

/** @var PhpDocInfo $phpDocInfo */
$entityTagValueNode = $phpDocInfo->getByType(EntityTagValueNode::class);
if (! $entityTagValueNode instanceof EntityTagValueNode) {
    return null;
}

var_dump($entityTagValueNode->getShortName()); // "@ORM\Entity"
```
