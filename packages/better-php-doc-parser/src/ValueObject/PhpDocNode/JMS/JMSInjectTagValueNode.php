<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class JMSInjectTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    /**
     * @var string|null
     */
    private $serviceName;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        array $items,
        ?string $serviceName,
        ?string $annotationContent
    ) {
        $this->serviceName = $serviceName;

        parent::__construct($arrayPartPhpDocTagPrinter, $tagValueNodePrinter, $items, $annotationContent);
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
