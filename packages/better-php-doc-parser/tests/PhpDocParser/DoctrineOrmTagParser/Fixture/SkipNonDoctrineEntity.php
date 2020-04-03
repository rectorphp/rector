<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

final class SkipNonDoctrineEntity
{
    /**
     * @Entity("user", expr="repository.findOneBy({'legalInformation.payment.uboDeclarationId': uboDeclarationId})")
     */
    public function run()
    {
    }
}
