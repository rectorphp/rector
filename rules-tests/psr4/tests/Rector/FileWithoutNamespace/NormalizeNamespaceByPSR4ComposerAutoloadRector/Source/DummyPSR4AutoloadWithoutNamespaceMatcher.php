<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source;

use PhpParser\Node;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;

final class DummyPSR4AutoloadWithoutNamespaceMatcher implements PSR4AutoloadNamespaceMatcherInterface
{
    public function getExpectedNamespace(Node $node): ?string
    {
        return 'Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture';
    }
}
