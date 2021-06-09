<?php

declare(strict_types=1);

namespace Rector\Tests\PhpAttribute\Printer;

use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Rector\Testing\PHPUnit\AbstractTestCase;

class PhpAttributeGroupFactoryTest extends AbstractTestCase
{
    private PhpAttributeGroupFactory $phpAttributeGroupFactory;

    protected function setUp(): void
    {
        $this->boot();

        $this->phpAttributeGroupFactory = $this->getService(PhpAttributeGroupFactory::class);
    }

    public function testCreateFromClassWithItems(): void
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClassWithItems(
            'Symfony\Component\Routing\Annotation\Route',
            ['path' => '/path', 'name' => 'action']
        );

        self::assertInstanceOf(AttributeGroup::class, $attributeGroup);
    }

    public function testCreateArgsFromItems(): void
    {
        $method = new \ReflectionMethod($this->phpAttributeGroupFactory, 'createArgsFromItems');
        $method->setAccessible(true);
        $args = $method->invokeArgs($this->phpAttributeGroupFactory, [['path' => '/path', 'name' => 'action']]);

        self::assertIsArray($args);
        self::assertCount(2, $args);
        self::assertContainsOnlyInstancesOf(Arg::class, $args);
    }
}
