<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\NodeTypeResolver\Tests\DependencyInjection\NodeTypeResolverContainerFactory;

abstract class AbstractNodeTypeResolverContainerAwareTestCase extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ContainerInterface
     */
    private static $cachedContainer;

    /**
     * Constructs a test case with the given name.
     *
     * @param mixed[] $data
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        if (self::$cachedContainer === null) {
            self::$cachedContainer = (new NodeTypeResolverContainerFactory())->create();
        }

        $this->container = self::$cachedContainer;

        parent::__construct($name, $data, $dataName);
    }
}
