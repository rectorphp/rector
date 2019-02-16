<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Class_\ClassReplacerRector;

use Manual\Twig\TwigFilter;
use Manual_Twig_Filter;
use Rector\Rector\Class_\ClassReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\AbstractManualExtension;
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClass;
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\NewClassWithoutTypo;
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\OldClass;
use Rector\Tests\Rector\Class_\ClassReplacerRector\Source\OldClassWithTypo;

/**
 * @see https://stackoverflow.com/a/35355700/1348344
 */
final class ClassReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/class_to_new.php.inc',
            __DIR__ . '/Fixture/class_to_interface.php.inc',
            __DIR__ . '/Fixture/interface_to_class.php.inc',
            __DIR__ . '/Fixture/name_insensitive.php.inc',
            __DIR__ . '/Fixture/twig_case.php.inc',
            __DIR__ . '/Fixture/keep_return_tag.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ClassReplacerRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            OldClass::class => NewClass::class,
            OldClassWithTypo::class => NewClassWithoutTypo::class,
            'DateTime' => 'DateTimeInterface',
            'Countable' => 'stdClass',
            Manual_Twig_Filter::class => TwigFilter::class,
            'Twig_AbstractManualExtension' => AbstractManualExtension::class,
        ];
    }
}
