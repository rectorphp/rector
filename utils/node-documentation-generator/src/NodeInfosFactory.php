<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator;

use Rector\Utils\NodeDocumentationGenerator\Category\CategoryResolver;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfo;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfos;

final class NodeInfosFactory
{
    /**
     * @var NodeCodeSampleProvider
     */
    private $nodeCodeSampleProvider;

    /**
     * @var CategoryResolver
     */
    private $categoryResolver;

    public function __construct(NodeCodeSampleProvider $nodeCodeSampleProvider, CategoryResolver $categoryResolver)
    {
        $this->nodeCodeSampleProvider = $nodeCodeSampleProvider;
        $this->categoryResolver = $categoryResolver;
    }

    public function create(): NodeInfos
    {
        $nodeInfos = [];
        foreach ($this->nodeCodeSampleProvider->provide() as $nodeClass => $nodeCodeSamples) {
            $category = $this->categoryResolver->resolveCategoryByNodeClass($nodeClass);
            $nodeInfos[] = new NodeInfo($nodeClass, $nodeCodeSamples, $category);
        }

        return new NodeInfos($nodeInfos);
    }
}
