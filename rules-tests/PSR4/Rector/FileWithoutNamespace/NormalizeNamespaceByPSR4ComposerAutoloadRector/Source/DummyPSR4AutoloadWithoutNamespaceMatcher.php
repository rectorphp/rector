<?php

declare(strict_types=1);

namespace Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Source;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;

final class DummyPSR4AutoloadWithoutNamespaceMatcher implements PSR4AutoloadNamespaceMatcherInterface
{
    public function getExpectedNamespace(File $file, Node $node): ?string
    {
        return 'Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\Fixture';
    }
}
