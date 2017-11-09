<?php declare(strict_types=1);

namespace Rector\BetterReflection\Tests\Reflector;

use Rector\BetterReflection\Reflection\ReflectionClass;
use Rector\BetterReflection\Reflector\ClassReflector;
use Rector\BetterReflection\Reflector\ClassReflectorFactory;
use Rector\Tests\AbstractContainerAwareTestCase;
use SplFileInfo;

final class ClassReflectorOnSourceTest extends AbstractContainerAwareTestCase
{
    /**
     * @var SplFileInfo
     */
    private $currentProcessedFileInfo;

    /**
     * @var ClassReflector
     */
    private $classReflector;

    protected function setUp(): void
    {
        /** @var ClassReflectorFactory $classReflectorFactory */
        $classReflectorFactory = $this->container->get(ClassReflectorFactory::class);

        $this->currentProcessedFileInfo = new SplFileInfo(__DIR__ . '/NotLoadedSource/SomeClass.php');
        $this->classReflector = $classReflectorFactory->createWithFile($this->currentProcessedFileInfo);
    }

    public function test(): void
    {
        $this->assertInstanceOf(ClassReflector::class, $this->classReflector);
    }

    public function testReflectClassThatIsNotLoaded(): void
    {
        $className = 'NotLoadedSource\SomeClass';

        $classReflection = $this->classReflector->reflect($className);

        $this->assertInstanceOf(ReflectionClass::class, $classReflection);
    }
}
