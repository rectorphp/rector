<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver\Tests\ConfigResolver\Source;

use RectorPrefix20210510\Symplify\SetConfigResolver\Contract\SetProviderInterface;
use RectorPrefix20210510\Symplify\SetConfigResolver\Provider\AbstractSetProvider;
use RectorPrefix20210510\Symplify\SetConfigResolver\ValueObject\Set;
use Symplify\SmartFileSystem\SmartFileInfo;
final class DummySetProvider extends AbstractSetProvider implements SetProviderInterface
{
    /**
     * @var Set[]
     */
    private $sets = [];
    public function __construct()
    {
        $this->sets[] = new Set('some_php_set', new SmartFileInfo(__DIR__ . '/../Source/some_php_set.php'));
    }
    /**
     * @return Set[]
     */
    public function provide() : array
    {
        return $this->sets;
    }
}
