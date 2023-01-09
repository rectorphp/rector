# 24 Rules Overview

## AddEntityIdByConditionRector

Add entity id with annotations when meets condition

:wrench: **configure it!**

- class: [`Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector`](../src/Rector/Class_/AddEntityIdByConditionRector.php)

```php
use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddEntityIdByConditionRector::class, [
        AddEntityIdByConditionRector::DETECTED_TRAITS => [
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait',
        ],
    ]);
};
```

↓

```diff
+use Doctrine\ORM\Mapping as ORM;
+
 class SomeClass
 {
     use SomeTrait;
+
+    /**
+      * @ORM\Id
+      * @ORM\Column(type="integer")
+      * @ORM\GeneratedValue(strategy="AUTO")
+      */
+     private $id;
+
+    public function getId(): int
+    {
+        return $this->id;
+    }
 }
```

<br>

## ChangeBigIntEntityPropertyToIntTypeRector

Change database type "bigint" for @var/type declaration to string

- class: [`Rector\Doctrine\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector`](../src/Rector/Property/ChangeBigIntEntityPropertyToIntTypeRector.php)

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

Change array to ArrayCollection in setParameters method of query builder

- class: [`Rector\Doctrine\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector`](../src/Rector/MethodCall/ChangeSetParametersArrayToArrayCollectionRector.php)

```diff
+use Doctrine\Common\Collections\ArrayCollection;
 use Doctrine\ORM\EntityRepository;
+use Doctrine\ORM\Query\Parameter;

 class SomeRepository extends EntityRepository
 {
     public function getSomething()
     {
         return $this
             ->createQueryBuilder('sm')
             ->select('sm')
             ->where('sm.foo = :bar')
-            ->setParameters([
-                'bar' => 'baz'
-            ])
+            ->setParameters(new ArrayCollection([
+                new  Parameter('bar', 'baz'),
+            ]))
             ->getQuery()
             ->getResult()
         ;
     }
 }
```

<br>

## ClassAnnotationToNamedArgumentConstructorRector

Decorate classic array-based class annotation with named parameters

- class: [`Rector\Doctrine\Rector\Class_\ClassAnnotationToNamedArgumentConstructorRector`](../src/Rector/Class_/ClassAnnotationToNamedArgumentConstructorRector.php)

```diff
+use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
+
 /**
  * @Annotation
+ * @NamedArgumentConstructor
  */
 class SomeAnnotation
 {
     /**
      * @var string
      */
     private $foo;

-    public function __construct(array $values)
+    public function __construct(string $foo)
     {
-        $this->foo = $values['foo'];
+        $this->foo = $foo;
     }
 }
```

<br>

## CorrectDefaultTypesOnEntityPropertyRector

Change default value types to match Doctrine annotation type

- class: [`Rector\Doctrine\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector`](../src/Rector/Property/CorrectDefaultTypesOnEntityPropertyRector.php)

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

- class: [`Rector\Doctrine\Rector\Property\DoctrineTargetEntityStringToClassConstantRector`](../src/Rector/Property/DoctrineTargetEntityStringToClassConstantRector.php)

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

- class: [`Rector\Doctrine\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector`](../src/Rector/Property/ImproveDoctrineCollectionDocTypeInEntityRector.php)

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

- class: [`Rector\Doctrine\Rector\Class_\InitializeDefaultEntityCollectionRector`](../src/Rector/Class_/InitializeDefaultEntityCollectionRector.php)

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

- class: [`Rector\Doctrine\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector`](../src/Rector/Property/MakeEntityDateTimePropertyDateTimeInterfaceRector.php)

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

- class: [`Rector\Doctrine\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector`](../src/Rector/ClassMethod/MakeEntitySetterNullabilityInSyncWithPropertyRector.php)

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

## ManagerRegistryGetManagerToEntityManagerRector

Changes ManagerRegistry intermediate calls directly to EntityManager calls

- class: [`Rector\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector`](../src/Rector/Class_/ManagerRegistryGetManagerToEntityManagerRector.php)

```diff
-use Doctrine\Common\Persistence\ManagerRegistry;
+use Doctrine\ORM\EntityManagerInterface;

 class CustomRepository
 {
     /**
-     * @var ManagerRegistry
+     * @var EntityManagerInterface
      */
-    private $managerRegistry;
+    private $entityManager;

-    public function __construct(ManagerRegistry $managerRegistry)
+    public function __construct(EntityManagerInterface $entityManager)
     {
-        $this->managerRegistry = $managerRegistry;
+        $this->entityManager = $entityManager;
     }

     public function run()
     {
-        $entityManager = $this->managerRegistry->getManager();
-        $someRepository = $entityManager->getRepository('Some');
+        $someRepository = $this->entityManager->getRepository('Some');
     }
 }
```

<br>

## MoveCurrentDateTimeDefaultInEntityToConstructorRector

Move default value for entity property to constructor, the safest place

- class: [`Rector\Doctrine\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector`](../src/Rector/Class_/MoveCurrentDateTimeDefaultInEntityToConstructorRector.php)

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

## RemoveRedundantDefaultClassAnnotationValuesRector

Removes redundant default values from Doctrine ORM annotations on class level

- class: [`Rector\Doctrine\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector`](../src/Rector/Class_/RemoveRedundantDefaultClassAnnotationValuesRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
- * @ORM\Entity(readOnly=false)
+ * @ORM\Entity()
  */
 class SomeClass
 {
 }
```

<br>

## RemoveRedundantDefaultPropertyAnnotationValuesRector

Removes redundant default values from Doctrine ORM annotations/attributes properties

- class: [`Rector\Doctrine\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector`](../src/Rector/Property/RemoveRedundantDefaultPropertyAnnotationValuesRector.php)

```diff
 use Doctrine\ORM\Mapping as ORM;

 /**
  * @ORM\Entity
  */
 class SomeClass
 {
     /**
      * @ORM\ManyToOne(targetEntity=Training::class)
-     * @ORM\JoinColumn(name="training", unique=false)
+     * @ORM\JoinColumn(name="training")
      */
     private $training;
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
+    /**
+     * @var \Doctrine\ORM\EntityManagerInterface
+     */
+    private $entityManager;
+
+    /**
+     * @var \Doctrine\ORM\EntityRepository<Project>
+     */
+    private $repository;
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

- class: [`Rector\Doctrine\Rector\Property\TypedPropertyFromColumnTypeRector`](../src/Rector/Property/TypedPropertyFromColumnTypeRector.php)

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

- class: [`Rector\Doctrine\Rector\Property\TypedPropertyFromDoctrineCollectionRector`](../src/Rector/Property/TypedPropertyFromDoctrineCollectionRector.php)

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

- class: [`Rector\Doctrine\Rector\Property\TypedPropertyFromToManyRelationTypeRector`](../src/Rector/Property/TypedPropertyFromToManyRelationTypeRector.php)

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

- class: [`Rector\Doctrine\Rector\Property\TypedPropertyFromToOneRelationTypeRector`](../src/Rector/Property/TypedPropertyFromToOneRelationTypeRector.php)

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
