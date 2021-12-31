<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use RectorPrefix20211231\Nette\Utils\Strings;
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
use RectorPrefix20211231\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionAndKernelRector\ChangeFileLoaderInExtensionAndKernelRectorTest
 *
 * Works best with https://github.com/migrify/config-transformer
 */
final class ChangeFileLoaderInExtensionAndKernelRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change XML loader to YAML in Bundle Extension', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isKernelOrExtensionClass($node)) {
            return null;
        }
        $this->validateConfiguration($this->from, $this->to);
        $oldFileLoaderClass = self::FILE_LOADERS_BY_TYPE[$this->from];
        $newFileLoaderClass = self::FILE_LOADERS_BY_TYPE[$this->to];
        $this->traverseNodesWithCallable($node->stmts, function (\PhpParser\Node $node) use($oldFileLoaderClass, $newFileLoaderClass) {
            if ($node instanceof \PhpParser\Node\Expr\New_) {
                if (!$this->isName($node->class, $oldFileLoaderClass)) {
                    return null;
                }
                $node->class = new \PhpParser\Node\Name\FullyQualified($newFileLoaderClass);
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
    private function isKernelOrExtensionClass(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if ($this->isObjectType($class, new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpKernel\\DependencyInjection\\Extension'))) {
            return \true;
        }
        return $this->isObjectType($class, new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpKernel\\Kernel'));
    }
    private function validateConfiguration(string $from, string $to) : void
    {
        if (!isset(self::FILE_LOADERS_BY_TYPE[$from])) {
            $message = \sprintf('File loader "%s" format is not supported', $from);
            throw new \Rector\Symfony\Exception\InvalidConfigurationException($message);
        }
        if (!isset(self::FILE_LOADERS_BY_TYPE[$to])) {
            $message = \sprintf('File loader "%s" format is not supported', $to);
            throw new \Rector\Symfony\Exception\InvalidConfigurationException($message);
        }
    }
    private function refactorLoadMethodCall(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Config\\Loader\\LoaderInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'load')) {
            return null;
        }
        $this->replaceSuffix($node, $this->from, $this->to);
        return $node;
    }
    private function replaceSuffix(\PhpParser\Node\Expr\MethodCall $methodCall, string $from, string $to) : void
    {
        // replace XML to YAML suffix in string parts
        $fileArgument = $methodCall->args[0]->value;
        $this->traverseNodesWithCallable([$fileArgument], function (\PhpParser\Node $node) use($from, $to) : ?Node {
            if (!$node instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            $node->value = \RectorPrefix20211231\Nette\Utils\Strings::replace($node->value, '#\\.' . $from . '$#', '.' . $to);
            return $node;
        });
    }
}
