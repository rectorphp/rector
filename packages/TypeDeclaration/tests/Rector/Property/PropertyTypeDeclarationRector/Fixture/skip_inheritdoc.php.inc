<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Source\SomeService;

class SkipInheritdocPhpdoc
{
    /**
     * @inheritdoc
     */
    private static $registryFirst = [];
    /**
     * @inheritDoc
     */
    private static $registrySecond = [];
    /**
     * {@inheritdoc}
     */
    private static $registryThird = [];
    /**
     * {@inheritDoc}
     */
    private static $registryFourth = [];
    private static $registryFifth = [];

    public static function register(SomeService $service)
    {
        self::$registryFirst[$service->getLabel()] = $service;
        self::$registrySecond[$service->getLabel()] = $service;
        self::$registryThird[$service->getLabel()] = $service;
        self::$registryFourth[$service->getLabel()] = $service;
        self::$registryFifth[$service->getLabel()] = $service;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Source\SomeService;

class SkipInheritdocPhpdoc
{
    /**
     * @inheritdoc
     */
    private static $registryFirst = [];
    /**
     * @inheritDoc
     */
    private static $registrySecond = [];
    /**
     * {@inheritdoc}
     */
    private static $registryThird = [];
    /**
     * {@inheritDoc}
     */
    private static $registryFourth = [];
    /**
     * @var mixed[]
     */
    private static $registryFifth = [];

    public static function register(SomeService $service)
    {
        self::$registryFirst[$service->getLabel()] = $service;
        self::$registrySecond[$service->getLabel()] = $service;
        self::$registryThird[$service->getLabel()] = $service;
        self::$registryFourth[$service->getLabel()] = $service;
        self::$registryFifth[$service->getLabel()] = $service;
    }
}

?>
