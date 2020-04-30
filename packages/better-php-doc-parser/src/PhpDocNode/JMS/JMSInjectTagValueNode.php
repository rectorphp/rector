<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use JMS\DiExtraBundle\Annotation\Inject;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class JMSInjectTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    /**
     * @var string|null
     */
    private $serviceName;

    public function __construct(Inject $inject, ?string $serviceName, ?string $annotationContent)
    {
        $this->items = get_object_vars($inject);
        $this->serviceName = $serviceName;

        $this->resolveOriginalContentSpacingAndOrder($annotationContent);
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function getShortName(): string
    {
        return '@DI\Inject';
    }

    public function getSilentKey(): string
    {
        return 'value';
    }
}
