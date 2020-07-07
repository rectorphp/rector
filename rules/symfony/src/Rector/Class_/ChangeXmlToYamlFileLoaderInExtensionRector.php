<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\Class_\ChangeXmlToYamlFileLoaderInExtensionRector\ChangeXmlToYamlFileLoaderInExtensionRectorTest
 */
final class ChangeXmlToYamlFileLoaderInExtensionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change XML loader to YAML in Bundle Extension', [
            new CodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP

            ),
        ]);
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
        if (! $this->isObjectType($node, 'Symfony\Component\HttpKernel\DependencyInjection\Extension')) {
            return null;
        }

        $loadClassMethod = $node->getMethod('load');
        if ($loadClassMethod === null) {
            return null;
        }

        $this->traverseNodesWithCallable((array) $loadClassMethod->stmts, function (Node $node) {
            if ($node instanceof New_) {
                if (! $this->isName($node->class, 'Symfony\Component\DependencyInjection\Loader\XmlFileLoader')) {
                    return null;
                }

                $node->class = new FullyQualified('Symfony\Component\DependencyInjection\Loader\YamlFileLoader');
                return $node;
            }

            return $this->refactorLoadMethodCall($node);
        });

        return $node;
    }

    private function refactorLoadMethodCall(Node $node): ?Node
    {
        if (! $node instanceof MethodCall) {
            return null;
        }

        if (! $node->var instanceof Variable) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Symfony\Component\DependencyInjection\Loader\XmlFileLoader')) {
            return null;
        }

        if (! $this->isName($node->name, 'load')) {
            return null;
        }

        $this->replaceXmlWithYamlSuffix($node);
        return $node;
    }

    private function replaceXmlWithYamlSuffix(MethodCall $methodCall): void
    {
        // replace XML to YAML suffix in string parts
        $fileArgument = $methodCall->args[0]->value;

        $this->traverseNodesWithCallable([$fileArgument], function (Node $node): ?Node {
            if (! $node instanceof String_) {
                return null;
            }

            $node->value = Strings::replace($node->value, '#\.xml$#', '.yaml');

            return $node;
        });
    }
}
