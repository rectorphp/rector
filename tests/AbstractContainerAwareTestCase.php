<?php declare(strict_types=1);

namespace Rector\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rector\DependencyInjection\ContainerFactory;

abstract class AbstractContainerAwareTestCase extends TestCase
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
     * @param string|null $data
     * @param mixed[] $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        if (self::$cachedContainer === null) {
            self::$cachedContainer = (new ContainerFactory())->create();
        }

        $this->container = self::$cachedContainer;

        parent::__construct($name, $data, $dataName);
    }
}
