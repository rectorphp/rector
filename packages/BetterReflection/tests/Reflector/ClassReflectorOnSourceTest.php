<?php declare(strict_types=1);

namespace Rector\BetterReflection\Tests\Reflector;

use Rector\BetterReflection\Reflector\ClassReflectorFactory;
use Rector\Tests\AbstractContainerAwareTestCase;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\ClassReflector;
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
    private $currentFileAwareClassReflector;

    protected function setUp(): void
    {
        /** @var ClassReflectorFactory $classReflectorFactory */
        $classReflectorFactory = $this->container->get(ClassReflectorFactory::class);

        $this->currentProcessedFileInfo = new SplFileInfo(__DIR__ . '/NotLoadedSource/SomeClass.php');
        $this->currentFileAwareClassReflector = $classReflectorFactory->createWithFile($this->currentProcessedFileInfo);
    }

    public function test(): void
    {
        $this->assertInstanceOf(ClassReflector::class, $this->currentFileAwareClassReflector);
    }

    public function testReflectClassThatIsNotLoaded(): void
    {
        $className = 'NotLoadedSource\SomeClass';

        $classReflection = $this->currentFileAwareClassReflector->reflect($className);

        $this->assertInstanceOf(ReflectionClass::class, $classReflection);
    }
}
