# 21 Rules Overview

## ChangeBigIntEntityPropertyToIntTypeRector

Change database type "bigint" for @var/type declaration to string

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector`](../rules/CodeQuality/Rector/Property/ChangeBigIntEntityPropertyToIntTypeRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity()
  */
 class SomeEntity
 {
     /**
-     * @var int|null
+     * @var string|null
      * @ORM\Column(type="bigint", nullable=true)
      */
     private $bigNumber;
 }
```

<br>

## ChangeCompositeExpressionAddMultipleWithWithRector

Change CompositeExpression ->addMultiple($parts) to ->with(...$parts)

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector`](../src/Rector/MethodCall/ChangeCompositeExpressionAddMultipleWithWithRector.php)

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

## ChangeSetParametersArrayToArrayCollectionRector

Change array to ArrayCollection in `setParameters()` method of query builder

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector`](../src/Rector/MethodCall/ChangeSetParametersArrayToArrayCollectionRector.php)

```diff
+use Doctrine\Common\Collections\ArrayCollection;
 use Doctrine\ORM\EntityRepository;
+use Doctrine\ORM\Query\Parameter;

 class SomeRepository extends EntityRepository
 {
     public function getSomething()
     {
         return $this->createQueryBuilder('sm')
             ->select('sm')
             ->where('sm.foo = :bar')
-            ->setParameters([
-                'bar' => 'baz'
-            ]);
+            ->setParameters(new ArrayCollection([
+                new  Parameter('bar', 'baz'),
+            ]));
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

## DoctrineTargetEntityStringToClassConstantRector

Convert targetEntities defined as String to <class>::class Constants in Doctrine Entities.

- class: [`Rector\Doctrine\CodeQuality\Rector\Property\DoctrineTargetEntityStringToClassConstantRector`](../rules/CodeQuality/Rector/Property/DoctrineTargetEntityStringToClassConstantRector.php)

```diff
 final class SomeClass
 {
     /**
-     * @ORM\OneToMany(targetEntity="AnotherClass")
+     * @ORM\OneToMany(targetEntity=\MyNamespace\Source\AnotherClass::class)
      */
     private readonly ?Collection $items;

-    #[ORM\ManyToOne(targetEntity: "AnotherClass")]
+    #[ORM\ManyToOne(targetEntity: \MyNamespace\Source\AnotherClass::class)]
     private readonly ?Collection $items2;
 }
```

<br>

## EntityAliasToClassConstantReferenceRector

Replaces doctrine alias with class.

:wrench: **configure it!**

- class: [`Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector`](../src/Rector/MethodCall/EntityAliasToClassConstantReferenceRector.php)

```php
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(EntityAliasToClassConstantReferenceRector::class, [
        EntityAliasToClassConstantReferenceRector::ALIASES_TO_NAMESPACES => [
            'App' => 'App\Entity',
        ],
    ]);
};
```

↓

```diff
 $entityManager = new Doctrine\ORM\EntityManager();
-$entityManager->getRepository("AppBundle:Post");
+$entityManager->getRepository(\App\Entity\Post::class);
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

## MoveRepositoryFromParentToConstructorRector

Turns parent EntityRepository class to constructor dependency

- class: [`Rector\Doctrine\Rector\Class_\MoveRepositoryFromParentToConstructorRector`](../src/Rector/Class_/MoveRepositoryFromParentToConstructorRector.php)

```diff
 namespace App\Repository;

+use App\Entity\Post;
 use Doctrine\ORM\EntityRepository;
+use Doctrine\ORM\EntityManagerInterface;

-final class PostRepository extends EntityRepository
+final class PostRepository
 {
+    /**
+     * @var \Doctrine\ORM\EntityRepository<Post>
+     */
+    private EntityRepository $repository;
+
+    public function __construct(EntityManagerInterface $entityManager)
+    {
+        $this->repository = $entityManager->getRepository(Post::class);
+    }
 }
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

## RemoveRepositoryFromEntityAnnotationRector

Removes repository class from `@Entity` annotation

- class: [`Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector`](../src/Rector/Class_/RemoveRepositoryFromEntityAnnotationRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
- * @ORM\Entity(repositoryClass="ProductRepository")
+ * @ORM\Entity
  */
 class Product
 {
 }
```

<br>

## ReplaceLifecycleEventArgsByDedicatedEventArgsRector

Replace `Doctrine\ORM\Event\LifecycleEventArgs` with specific event classes based on the function call

- class: [`Rector\Doctrine\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector`](../src/Rector/Param/ReplaceLifecycleEventArgsByDedicatedEventArgsRector.php)

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

## ReplaceParentRepositoryCallsByRepositoryPropertyRector

Handles method calls in child of Doctrine EntityRepository and moves them to `$this->repository` property.

- class: [`Rector\Doctrine\Rector\MethodCall\ReplaceParentRepositoryCallsByRepositoryPropertyRector`](../src/Rector/MethodCall/ReplaceParentRepositoryCallsByRepositoryPropertyRector.php)

```diff
 use Doctrine\ORM\EntityRepository;

 class SomeRepository extends EntityRepository
 {
     public function someMethod()
     {
-        return $this->findAll();
+        return $this->repository->findAll();
     }
 }
```

<br>

## ServiceEntityRepositoryParentCallToDIRector

Change ServiceEntityRepository to dependency injection, with repository property

- class: [`Rector\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector`](../src/Rector/ClassMethod/ServiceEntityRepositoryParentCallToDIRector.php)

```diff
 use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
 use Doctrine\Persistence\ManagerRegistry;

 final class ProjectRepository extends ServiceEntityRepository
 {
-    public function __construct(ManagerRegistry $registry)
+    private \Doctrine\ORM\EntityManagerInterface $entityManager;
+
+    /**
+     * @var \Doctrine\ORM\EntityRepository<Project>
+     */
+    private \Doctrine\ORM\EntityRepository $repository;
+
+    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
     {
-        parent::__construct($registry, Project::class);
+        $this->repository = $entityManager->getRepository(Project::class);
+        $this->entityManager = $entityManager;
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
