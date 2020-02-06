<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Scan;

use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Core\Scan\ScannedErrorToRectorResolver;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ScannedErrorToRectorResolverTest extends AbstractKernelTestCase
{
    /**
     * @var ScannedErrorToRectorResolver
     */
    private $scannedErrorToRectorResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->scannedErrorToRectorResolver = self::$container->get(ScannedErrorToRectorResolver::class);
    }

    public function testParam(): void
    {
        $errors = [];
        $errors[] = 'Declaration of Kedlubna\extendTest::add($message) should be compatible with Kedlubna\test::add(string $message = \'\')';

        $rectorConfiguration = $this->scannedErrorToRectorResolver->processErrors($errors);

        $expectedConfiguration = [
            'services' => [
                AddParamTypeDeclarationRector::class => [
                    '$typehintForParameterByMethodByClass' => [
                        'Kedlubna\extendTest' => [
                            'add' => [
                                0 => 'string',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedConfiguration, $rectorConfiguration);
    }

    public function testReturn(): void
    {
        $errors = [];
        $errors[] = 'Declaration of AAA\extendTest::nothing() must be compatible with AAA\test::nothing(): void;';

        $rectorConfiguration = $this->scannedErrorToRectorResolver->processErrors($errors);

        $expectedConfiguration = [
            'services' => [
                AddReturnTypeDeclarationRector::class => [
                    '$typehintForMethodByClass' => [
                        'AAA\extendTest' => [
                            'nothing' => 'void',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedConfiguration, $rectorConfiguration);
    }
}
