<?php declare(strict_types=1);

namespace Rector\Shopware\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/shopware/shopware/blob/5.6/UPGRADE-5.6.md
 * @see \Rector\Shopware\Tests\Rector\ClassConstFetch\ShopwareVersionConstsRector\ShopwareVersionConstsRectorTest
 */
final class ShopwareVersionConstsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use version from di parameter', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        echo \Shopware::VERSION;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        echo Shopware()->Container()->getParameter('shopware.release.version');
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
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'Shopware')) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        switch ($node->name->name) {
            case 'VERSION':
                return $this->buildParameterCall('shopware.release.version');
            case 'VERSION_TEXT':
                return $this->buildParameterCall('shopware.release.version_text');
            case 'REVISION':
                return $this->buildParameterCall('shopware.release.revision');
            default:
                return null;
        }
    }

    private function buildParameterCall(string $paramterName): MethodCall
    {
        $shopwareFunction = new FuncCall(new Name('Shopware'));
        $containerCall = new MethodCall($shopwareFunction, new Identifier('Container'));
        return new MethodCall($containerCall, new Identifier('getParameter'), [
            new Arg(new String_($paramterName)),
        ]);
    }
}
