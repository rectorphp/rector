<?php declare(strict_types=1);

namespace Rector\Examples;

use PhpParser\BuilderFactory;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Yaml\Yaml;

final class ConfiguredProvideConfigRector extends AbstractRector
{
    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(ClassMaintainer $classMaintainer, BuilderFactory $builderFactory)
    {
        $this->classMaintainer = $classMaintainer;
        $this->builderFactory = $builderFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify tests', [new CodeSample('', '')]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, AbstractRectorTestCase::class) || $node->isAbstract()) {
            return null;
        }

        $classMethodsByName = $this->classMaintainer->getMethodsByName($node);
        if (! isset($classMethodsByName['provideConfig'])) {
            return null;
        }

        // already changed
        if (isset($classMethodsByName['getRectorConfiguration'])) {
            return null;
        }

        $provideConfigMethod = $classMethodsByName['provideConfig'];

        if ($provideConfigMethod->stmts[0] instanceof Return_) {
            /** @var Return_ $returnNode */
            $returnNode = $provideConfigMethod->stmts[0];

            $configPath = $this->getValue($returnNode->expr);
            $yaml = Yaml::parseFile($configPath);

            $resolved = $this->resolveClassToConfigurationFromConfig($yaml);
            if ($resolved === null) {
                return null;
            }

            [$class, $configuration] = $resolved;

            $returnNode = new Return_($this->createClassConstant($class, 'class'));
            $node->stmts[] = $this->builderFactory->method('getRectorClass')
                ->makeProtected()
                ->setReturnType('string')
                ->addStmt($returnNode)
                ->getNode();

            $returnNode = new Return_(BuilderHelpers::normalizeValue($configuration));
            $node->stmts[] = $this->builderFactory->method('getRectorConfiguration')
                ->makeProtected()
                ->setReturnType('array')
                ->setDocComment("/**\n * @return mixed[]\n */")
                ->addStmt($returnNode)
                ->getNode();

            $this->removeNode($provideConfigMethod);

            // remove config file
            unlink($configPath);
        }

        return $node;
    }

    /**
     * @param mixed[] $yaml
     * @return mixed[]|null
     */
    private function resolveClassToConfigurationFromConfig(array $yaml): ?array
    {
        if (count($yaml) !== 1) {
            return null;
        }

        if (!isset($yaml['services'])) {
            return null;
        }

        // has only "services" keys
        if (count($yaml['services']) !== 1) {
            return null;
        }

        $class = key($yaml['services']);
        // only service
        $configuration = array_pop($yaml['services']);

        return [$class, $configuration];
    }
}
