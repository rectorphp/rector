# 15 Rules Overview

## ChangeCompositeExpressionAddMultipleWithWithRector

Change CompositeExpression ->addMultiple($parts) to ->with(...$parts)

- class: [`Rector\Doctrine\Dbal40\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector`](../rules/Dbal40/Rector/MethodCall/ChangeCompositeExpressionAddMultipleWithWithRector.php)

```diff
 use Doctrine\ORM\EntityRepository;
 use Doctrine\DBAL\Query\Expression\CompositeExpression;

 class SomeRepository extends EntityRepository
 {
     public function getSomething($parts)
     {
         $compositeExpression = CompositeExpression::and('', ...$parts);
-        $compositeExpression->addMultiple($parts);
+        $compositeExpression->with(...$parts);
     }
 }
```

<br>

## CorrectDefaultTypesOnEntityPropertyRector

Change default value types to match Doctrine annotation type

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector`](../rules/CodeQuality/Rector/Property/CorrectDefaultTypesOnEntityPropertyRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
     /**
      * @ORM\Column(name="is_old", type="boolean")
      */
-    private $isOld = '0';
+    private $isOld = false;
 }
```

<br>

## EventSubscriberInterfaceToAttributeRector

Replace EventSubscriberInterface with AsDoctrineListener attribute(s)

- class: [`Rector\Doctrine\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector`](../rules/Bundle210/Rector/Class_/EventSubscriberInterfaceToAttributeRector.php)

```diff
+use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
 use Doctrine\ORM\Event\PrePersistEventArgs;
 use Doctrine\ORM\Event\PostUpdateEventArgs;
-use Doctrine\Common\EventSubscriber;
 use Doctrine\ORM\Events;

-class MyEventSubscriber implements EventSubscriber
+#[AsDoctrineListener(event: Events::postUpdate)]
+#[AsDoctrineListener(event: Events::prePersist)]
+class MyEventSubscriber
 {
-    public function getSubscribedEvents()
-    {
-        return array(
-            Events::postUpdate,
-            Events::prePersist,
-        );
-    }
-
     public function postUpdate(PostUpdateEventArgs $args)
     {
         // ...
     }

     public function prePersist(PrePersistEventArgs $args)
     {
         // ...
     }
 }
```

<br>

## ImproveDoctrineCollectionDocTypeInEntityRector

Improve @var, `@param` and `@return` types for Doctrine collections to make them useful both for PHPStan and PHPStorm

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector`](../rules/CodeQuality/Rector/Property/ImproveDoctrineCollectionDocTypeInEntityRector.php)

```diff
 use Doctrine\Common\Collections\Collection;
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\OneToMany(targetEntity=Trainer::class, mappedBy="trainer")
-     * @var Collection|Trainer[]
+     * @var Collection<int, Trainer>|Trainer[]
      */
     private $trainings = [];
 }
```

<br>

## InitializeDefaultEntityCollectionRector

Initialize collection property in Entity constructor

- class: [`Rector\Doctrine\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector`](../rules/CodeQuality/Rector/Class_/InitializeDefaultEntityCollectionRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\OneToMany(targetEntity="MarketingEvent")
      */
     private $marketingEvents = [];
+
+    public function __construct()
+    {
+        $this->marketingEvents = new ArrayCollection();
+    }
 }
```

<br>

## MakeEntityDateTimePropertyDateTimeInterfaceRector

Make maker bundle generate DateTime property accept DateTimeInterface too

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector`](../rules/CodeQuality/Rector/Property/MakeEntityDateTimePropertyDateTimeInterfaceRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
     /**
-     * @var DateTime|null
+     * @var DateTimeInterface|null
      */
     private $bornAt;

     public function setBornAt(DateTimeInterface $bornAt)
     {
         $this->bornAt = $bornAt;
     }
 }
```

<br>

## MakeEntitySetterNullabilityInSyncWithPropertyRector

Make nullability in setter class method with respect to property

- class: [`Rector\Doctrine\CodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector`](../rules/CodeQuality/Rector/ClassMethod/MakeEntitySetterNullabilityInSyncWithPropertyRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class Product
 {
     /**
      * @ORM\ManyToOne(targetEntity="AnotherEntity")
      * @ORM\JoinColumn(nullable=false)
      */
     private $anotherEntity;

-    public function setAnotherEntity(?AnotherEntity $anotherEntity)
+    public function setAnotherEntity(AnotherEntity $anotherEntity)
     {
         $this->anotherEntity = $anotherEntity;
     }
 }
```

<br>

## MoveCurrentDateTimeDefaultInEntityToConstructorRector

Move default value for entity property to constructor, the safest place

- class: [`Rector\Doctrine\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector`](../rules/CodeQuality/Rector/Class_/MoveCurrentDateTimeDefaultInEntityToConstructorRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class User
 {
     /**
      * @var DateTimeInterface
      *
-     * @ORM\Column(type="datetime", nullable=false, options={"default"="now()"})
+     * @ORM\Column(type="datetime", nullable=false)
      */
-    private $when = 'now()';
+    private $when;
+
+    public function __construct()
+    {
+        $this->when = new \DateTime();
+    }
 }
```

<br>

## OrderByKeyToClassConstRector

Replace OrderBy Attribute ASC/DESC with class constant from Criteria

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\OrderByKeyToClassConstRector`](../rules/CodeQuality/Rector/Property/OrderByKeyToClassConstRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 class ReplaceOrderByAscWithClassConstant
 {
-    #[ORM\OrderBy(['createdAt' => 'ASC'])]
+    #[ORM\OrderBy(['createdAt' => \Doctrine\Common\Collections\Criteria::ASC])]
     protected \DateTimeInterface $messages;
 }
 ?>
```

<br>

## RemoveEmptyTableAttributeRector

Remove empty Table attribute on entities because it's useless

- class: [`Rector\Doctrine\CodeQuality\Rector\Class_\RemoveEmptyTableAttributeRector`](../rules/CodeQuality/Rector/Class_/RemoveEmptyTableAttributeRector.php)

```diff
 <?php

 use Doctrine\ORM\Mapping as ORM;

-#[ORM\Table]
 #[ORM\Entity]
 class Product
 {
 }
```

<br>

## ReplaceLifecycleEventArgsByDedicatedEventArgsRector

Replace `Doctrine\ORM\Event\LifecycleEventArgs` with specific event classes based on the function call

- class: [`Rector\Doctrine\Orm214\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector`](../rules/Orm214/Rector/Param/ReplaceLifecycleEventArgsByDedicatedEventArgsRector.php)

```diff
-use Doctrine\ORM\Event\LifecycleEventArgs;
+use Doctrine\ORM\Event\PrePersistEventArgs;

 class PrePersistExample
 {
-    public function prePersist(LifecycleEventArgs $args)
+    public function prePersist(PrePersistEventArgs $args)
     {
         // ...
     }
 }
```

<br>

## TypedPropertyFromColumnTypeRector

Complete `@var` annotations or types based on @ORM\Column

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector`](../rules/CodeQuality/Rector/Property/TypedPropertyFromColumnTypeRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 class SimpleColumn
 {
     /**
      * @ORM\Column(type="string")
      */
-    private $name;
+    private string|null $name = null;
 }
```

<br>

## TypedPropertyFromDoctrineCollectionRector

Add typed property based on Doctrine collection

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromDoctrineCollectionRector`](../rules/CodeQuality/Rector/Property/TypedPropertyFromDoctrineCollectionRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;
 use App\Entity\TrainingTerm;

 /**
  * @ORM\Entity
  */
 class DoctrineCollection
 {
     /**
      * @ORM\OneToMany(targetEntity="App\Entity\TrainingTerm", mappedBy="training")
      * @var TrainingTerm[]|Collection
      */
-    private $trainingTerms;
+    private \Doctrine\Common\Collections\Collection $trainingTerms;
 }
```

<br>

## TypedPropertyFromToManyRelationTypeRector

Complete `@var` annotations or types based on @ORM\*toMany annotations or attributes

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromToManyRelationTypeRector`](../rules/CodeQuality/Rector/Property/TypedPropertyFromToManyRelationTypeRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 class SimpleColumn
 {
     /**
      * @ORM\OneToMany(targetEntity="App\Product")
+     * @var \Doctrine\Common\Collections\Collection<\App\Product>
      */
-    private $products;
+    private \Doctrine\Common\Collections\Collection $products;
 }
```

<br>

## TypedPropertyFromToOneRelationTypeRector

Complete `@var` annotations or types based on @ORM\*toOne annotations or attributes

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\TypedPropertyFromToOneRelationTypeRector`](../rules/CodeQuality/Rector/Property/TypedPropertyFromToOneRelationTypeRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 class SimpleColumn
 {
     /**
      * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
      */
-    private $company;
+    private ?\App\Company\Entity\Company $company = null;
 }
```

<br>
