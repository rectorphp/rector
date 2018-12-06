<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector;

use Rector\Rector\Typehint\ParentTypehintedArgumentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ClassMetadataFactory;
use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ParserInterface;

final class ParentTypehintedArgumentRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [__DIR__ . '/Fixture/SomeClassImplementingParserInterface.php', __DIR__ . '/Fixture/MyMetadataFactory.php']
        );
    }

    protected function getRectorClass(): string
    {
        return ParentTypehintedArgumentRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            ParserInterface::class => ['parse' => ['code' => 'string']],
            ClassMetadataFactory::class => ['setEntityManager' => ['em' => 'Doctrine\ORM\EntityManagerInterface']],
        ];
    }
}
