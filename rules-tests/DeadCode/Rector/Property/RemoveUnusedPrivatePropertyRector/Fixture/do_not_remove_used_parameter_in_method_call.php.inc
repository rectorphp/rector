<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Source\AbstractSetting;

final class DemoSetting extends AbstractSetting
{
    private PageInterface $page;
    private SectionInterface $section;

	public function __construct(PageInterface $page, SectionInterface $section)
    {
        $this->page = $page;
        $this->section = $section;

        parent::__construct($page, $section);
    }

    public function getPage()
    {
        return $this->page;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Source\AbstractSetting;

final class DemoSetting extends AbstractSetting
{
    private PageInterface $page;

	public function __construct(PageInterface $page, SectionInterface $section)
    {
        $this->page = $page;

        parent::__construct($page, $section);
    }

    public function getPage()
    {
        return $this->page;
    }
}

?>
