<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PHPUnit\Framework\TestCase;
use Addwiki\Mediawiki\DataModel\Content;
use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Revision;
use Addwiki\Mediawiki\DataModel\Title;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    /**
     * @return array<int, array<int|string|Content&MockObject|EditInfo&MockObject|PageIdentifier|null>>
     */
    public function provideValidConstruction(): array {
        $mockEditInfo = $this->getMockBuilder( EditInfo::class )
            ->disableOriginalConstructor()
            ->getMock();
        $mockTitle = $this->getMockBuilder( Title::class )
            ->disableOriginalConstructor()
            ->getMock();

        return [
            [ "a", new PageIdentifier( $mockTitle ), 1 , $mockEditInfo, 'foo', null ],
            [ 1, new PageIdentifier( $mockTitle, 3 ), 1 , $mockEditInfo, 'foo', '20141212121212' ],
        ];
    }
}
