<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Tests\OutputFormatter\CodeSamplePrinter;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\DocumentationGenerator\OutputFormatter\CodeSamplePrinter;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use ReflectionClass;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class CodeSamplePrinterTest extends AbstractKernelTestCase
{
    /**
     * @var CodeSamplePrinter
     */
    private $codeSamplePrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->codeSamplePrinter = self::$container->get(CodeSamplePrinter::class);
    }

    public function test(): void
    {
        $rectorClass = TypedPropertyRector::class;
        $reflectionClass = new ReflectionClass($rectorClass);

        /** @var TypedPropertyRector $typedPropertyRector */
        $typedPropertyRector = $reflectionClass->newInstanceWithoutConstructor();

        $printedCodeSamples = $this->codeSamplePrinter->printCodeSamples(
            $typedPropertyRector->getDefinition(),
            $typedPropertyRector
        );

        dump($printedCodeSamples);
        die;
    }
}
