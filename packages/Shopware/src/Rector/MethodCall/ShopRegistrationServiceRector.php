<?php declare(strict_types=1);

namespace Rector\Shopware\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
 * @see \Rector\Shopware\Tests\Rector\MethodCall\ShopRegistrationServiceRector\ShopRegistrationServiceRectorTest
 */
final class ShopRegistrationServiceRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace $shop->registerResources() with ShopRegistrationService', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $shop = new \Shopware\Models\Shop\Shop();
        $shop->registerResources();
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $shop = new \Shopware\Models\Shop\Shop();
        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'registerResources')) {
            return null;
        }

        if (! $this->isObjectType($node, 'Shopware\Models\Shop\Shop')) {
            return null;
        }

        $shopwareFunction = new FuncCall(new Name('Shopware'));
        $containerCall = new MethodCall($shopwareFunction, new Identifier('Container'));
        $methodCall = new MethodCall($containerCall, new Identifier('get'), [
            new Arg(new String_('shopware.components.shop_registration_service')),
        ]);

        return new MethodCall($methodCall, new Identifier('registerShop'), [new Arg($node->var)]);
    }
}
