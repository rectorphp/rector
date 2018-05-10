<?php declare(strict_types=1);

namespace Rector\BetterReflection\Tests\Reflector;

use Rector\BetterReflection\Reflector\ClassReflectorFactory;
use Rector\Tests\AbstractContainerAwareTestCase;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\ClassReflector;

final class ClassReflectorOnSourceTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ClassReflector
     */
    private $classReflector;

    protected function setUp(): void
    {
        /** @var ClassReflectorFactory $classReflectorFactory */
        $classReflectorFactory = $this->container->get(ClassReflectorFactory::class);

        $this->classReflector = $classReflectorFactory->createWithSource([__DIR__ . '/NotLoadedSource/SomeClass.php']);
    }

    public function testReflectClassThatIsNotLoaded(): void
    {
        $classReflection = $this->classReflector->reflect('NotLoadedSource\SomeClass');

        $this->assertInstanceOf(ReflectionClass::class, $classReflection);
    }
}
