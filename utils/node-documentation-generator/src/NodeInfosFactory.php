<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator;

use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfo;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfos;

final class NodeInfosFactory
{
    /**
     * @var NodeCodeSampleProvider
     */
    private $nodeCodeSampleProvider;

    public function __construct(NodeCodeSampleProvider $nodeCodeSampleProvider)
    {
        $this->nodeCodeSampleProvider = $nodeCodeSampleProvider;
    }

    public function create(): NodeInfos
    {
        $nodeInfos = [];
        foreach ($this->nodeCodeSampleProvider->provide() as $nodeClass => $nodeCodeSamples) {
            $nodeInfos[] = new NodeInfo($nodeClass, $nodeCodeSamples);
        }

        return new NodeInfos($nodeInfos);
    }
}
