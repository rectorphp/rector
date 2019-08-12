<?php declare(strict_types=1);

namespace Rector\Extension;

use Rector\Contract\Extension\RectorFinishExtensionInterface;

final class RectorFinishExtensionRunner
{
    /**
     * @var RectorFinishExtensionInterface[]
     */
    private $rectorFinishExtensions = [];

    /**
     * @param RectorFinishExtensionInterface[] $rectorFinishExtensions
     */
    public function __construct(array $rectorFinishExtensions = [])
    {
        $this->rectorFinishExtensions = $rectorFinishExtensions;
    }

    public function run(): void
    {
        foreach ($this->rectorFinishExtensions as $rectorFinishExtension) {
            $rectorFinishExtension->run();
        }
    }
}
