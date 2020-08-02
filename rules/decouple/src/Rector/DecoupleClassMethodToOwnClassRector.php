<?php

declare(strict_types=1);

namespace Rector\Decouple\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Decouple\Matcher\DecoupledClassMethodMatcher;
use Rector\Decouple\NodeFactory\ConstructorClassMethodFactory;
use Rector\Decouple\NodeFactory\NamespaceFactory;
use Rector\Decouple\UsedNodesExtractor\UsedClassConstsExtractor;
use Rector\Decouple\UsedNodesExtractor\UsedClassMethodsExtractor;
use Rector\Decouple\UsedNodesExtractor\UsedClassPropertyExtractor;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Decouple\Tests\Rector\DecoupleClassMethodToOwnClassRector\DecoupleClassMethodToOwnClassRectorTest
 */
final class DecoupleClassMethodToOwnClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_NAMES_BY_CLASS = '$methodNamesByClass';

    /**
     * @var mixed[][]
     */
    private $methodNamesByClass = [];

    /**
     * @var NamespaceFactory
     */
    private $namespaceFactory;

    /**
     * @var UsedClassMethodsExtractor
     */
    private $usedClassMethodsExtractor;

    /**
     * @var UsedClassConstsExtractor
     */
    private $usedClassConstsExtractor;

    /**
     * @var UsedClassPropertyExtractor
     */
    private $usedClassPropertyExtractor;

    /**
     * @var DecoupledClassMethodMatcher
     */
    private $decoupledClassMethodMatcher;

    /**
     * @var ConstructorClassMethodFactory
     */
    private $constructorClassMethodFactory;

    public function __construct(
        NamespaceFactory $namespaceFactory,
        UsedClassMethodsExtractor $usedClassMethodsExtractor,
        UsedClassConstsExtractor $usedClassConstsExtractor,
        UsedClassPropertyExtractor $usedClassPropertyExtractor,
        DecoupledClassMethodMatcher $decoupledClassMethodMatcher,
        ConstructorClassMethodFactory $constructorClassMethodFactory
    ) {
        $this->namespaceFactory = $namespaceFactory;
        $this->usedClassMethodsExtractor = $usedClassMethodsExtractor;
        $this->usedClassConstsExtractor = $usedClassConstsExtractor;
        $this->usedClassPropertyExtractor = $usedClassPropertyExtractor;
        $this->decoupledClassMethodMatcher = $decoupledClassMethodMatcher;
        $this->constructorClassMethodFactory = $constructorClassMethodFactory;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $decoupleClassMethodMatch = $this->decoupledClassMethodMatcher->matchDecoupled(
            $node,
            $this->methodNamesByClass
        );
        if ($decoupleClassMethodMatch === null) {
            return null;
        }

        $mainClassMethod = clone $node;
        $mainClassMethod->name = new Identifier($decoupleClassMethodMatch->getMethodName());
        $this->makePublic($mainClassMethod);

        // 2. get related class constants in the same class
        $usedClassConsts = $this->usedClassConstsExtractor->extract($node);

        // 3. get class method related methods call in the same class
        $usedClassMethods = $this->usedClassMethodsExtractor->extractFromClassMethod(
            $node,
            $decoupleClassMethodMatch->getParentClassName()
        );

        // 4. get class method related property fetches in the same class - add to constructor
        $classMethods = array_merge($usedClassMethods, [$node]);
        $usedProperties = $this->usedClassPropertyExtractor->extractFromClassMethods(
            $classMethods,
            $decoupleClassMethodMatch->getParentClassName()
        );

        // 5. add constructor dependencies $requiredLocalPropertyFetches
        /** @var Class_ $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        $constructClassMethod = $this->constructorClassMethodFactory->create($usedProperties);

        // 6. build a class
        $usedClassStmts = array_merge(
            $usedClassConsts,
            $usedProperties,
            $constructClassMethod !== null ? [$constructClassMethod] : [],
            [$mainClassMethod],
            $usedClassMethods
        );

        $namespace = $this->namespaceFactory->createNamespacedClassByNameAndStmts(
            $classLike,
            $decoupleClassMethodMatch,
            $usedClassStmts
        );

        $newClassLocation = $this->createNewClassLocation($node, $decoupleClassMethodMatch->getClassName());
        $this->printNodesToFilePath($namespace, $newClassLocation);

        // 7. cleanup this class method
        $this->removeNode($node);

        return null;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move class method with its all dependencies to own class by method name', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function someMethod()
    {
        $this->alsoCallThis();
    }

    private function alsoCallThis()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
                ,
                [
                    self::METHOD_NAMES_BY_CLASS => [
                        'SomeClass' => [
                            'someMethod' => [
                                'class' => 'NewDecoupledClass',
                                'method' => 'someRenamedMethod',
                                'parent_class' => 'AddedParentClass',
                            ],
                        ],
                    ],
                ],
                <<<'CODE_SAMPLE'
<?php

class NewDecoupledClass extends AddedParentClass
{
    public function someRenamedMethod()
    {
        $this->alsoCallThis();
    }

    private function alsoCallThis()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function configure(array $configuration): void
    {
        $this->methodNamesByClass = $configuration[self::METHOD_NAMES_BY_CLASS] ?? [];
    }

    private function createNewClassLocation(ClassMethod $classMethod, string $newClassName): string
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $classMethod->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            throw new ShouldNotHappenException();
        }

        return $fileInfo->getPath() . DIRECTORY_SEPARATOR . $newClassName . '.php';
    }
}
