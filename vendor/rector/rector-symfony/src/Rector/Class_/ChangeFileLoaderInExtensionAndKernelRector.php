<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Exception\InvalidConfigurationException;
use RectorPrefix202305\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use RectorPrefix202305\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use RectorPrefix202305\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector\ChangeFileLoaderInExtensionAndKernelRectorTest
 *
 * Works best with https://github.com/migrify/config-transformer
 */
final class ChangeFileLoaderInExtensionAndKernelRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FROM = 'from';
    /**
     * @var string
     */
    public const TO = 'to';
    /**
     * @var array<string, class-string<PhpFileLoader>|class-string<XmlFileLoader>|class-string<YamlFileLoader>>
     */
    private const FILE_LOADERS_BY_TYPE = ['xml' => 'Symfony\\Component\\DependencyInjection\\Loader\\XmlFileLoader', 'yaml' => 'Symfony\\Component\\DependencyInjection\\Loader\\YamlFileLoader', 'php' => 'Symfony\\Component\\DependencyInjection\\Loader\\PhpFileLoader'];
    /**
     * @var string
     */
    private $from;
    /**
     * @var string
     */
    private $to;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change XML loader to YAML in Bundle Extension', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SomeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator());
        $loader->load(__DIR__ . '/../Resources/config/controller.xml');
        $loader->load(__DIR__ . '/../Resources/config/events.xml');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class SomeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator());
        $loader->load(__DIR__ . '/../Resources/config/controller.yaml');
        $loader->load(__DIR__ . '/../Resources/config/events.yaml');
    }
}
CODE_SAMPLE
, [self::FROM => 'xml', self::TO => 'yaml'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isKernelOrExtensionClass($node)) {
            return null;
        }
        $this->validateConfiguration($this->from, $this->to);
        $oldFileLoaderClass = self::FILE_LOADERS_BY_TYPE[$this->from];
        $newFileLoaderClass = self::FILE_LOADERS_BY_TYPE[$this->to];
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use($oldFileLoaderClass, $newFileLoaderClass) {
            if ($node instanceof New_) {
                if (!$this->isName($node->class, $oldFileLoaderClass)) {
                    return null;
                }
                $node->class = new FullyQualified($newFileLoaderClass);
                return $node;
            }
            return $this->refactorLoadMethodCall($node);
        });
        return $node;
    }
    public function configure(array $configuration) : void
    {
        $this->from = $configuration[self::FROM];
        $this->to = $configuration[self::TO];
    }
    private function isKernelOrExtensionClass(Class_ $class) : bool
    {
        if ($this->isObjectType($class, new ObjectType('Symfony\\Component\\HttpKernel\\DependencyInjection\\Extension'))) {
            return \true;
        }
        return $this->isObjectType($class, new ObjectType('Symfony\\Component\\HttpKernel\\Kernel'));
    }
    private function validateConfiguration(string $from, string $to) : void
    {
        if (!isset(self::FILE_LOADERS_BY_TYPE[$from])) {
            $message = \sprintf('File loader "%s" format is not supported', $from);
            throw new InvalidConfigurationException($message);
        }
        if (!isset(self::FILE_LOADERS_BY_TYPE[$to])) {
            $message = \sprintf('File loader "%s" format is not supported', $to);
            throw new InvalidConfigurationException($message);
        }
    }
    private function refactorLoadMethodCall(Node $node) : ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }
        if (!$node->var instanceof Variable) {
            return null;
        }
        if (!$this->isName($node->name, 'load')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Config\\Loader\\LoaderInterface'))) {
            return null;
        }
        $this->replaceSuffix($node, $this->from, $this->to);
        return $node;
    }
    private function replaceSuffix(MethodCall $methodCall, string $from, string $to) : void
    {
        // replace XML to YAML suffix in string parts
        $fileArgument = $methodCall->getArgs()[0]->value;
        $this->traverseNodesWithCallable([$fileArgument], function (Node $node) use($from, $to) : ?Node {
            if (!$node instanceof String_) {
                return null;
            }
            $node->value = Strings::replace($node->value, '#\\.' . $from . '$#', '.' . $to);
            return $node;
        });
    }
}
