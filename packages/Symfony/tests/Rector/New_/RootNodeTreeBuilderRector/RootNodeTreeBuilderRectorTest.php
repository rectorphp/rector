<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector;

use Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Source\TreeBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RootNodeTreeBuilderRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RootNodeTreeBuilderRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$treeBuilderClass' => TreeBuilder::class];
    }
}
