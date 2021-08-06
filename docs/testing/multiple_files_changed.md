# Multiple files changed

Sometimes Rector does changes in multiple files. How to test it?

## Rector
Add file with content in Rector:

```php
$addedFileWithContent = new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($filePath, $content);
$this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
```

## RectorTest
In RectorTest just use
```php
$this->doTestFileInfoWithAdditionalChanges($fileInfo);
```
instead of

```php
$this->doTestFileInfo($fileInfo);
```

## Test fixtures
Fixture contains more parts separated by `-----` like in classic tests:
```
1) original content
-----
2) expected content (keep empty if no change should happen)
-----
3) path to another changed file (relative to file processed)
-----
4) original content of another file (keep empty if file doesn't exist yet)
-----
5) expected content of another file
```

Parts 3-5 can be multiplied in case there are more files created / updated.

### Fixture examples
Example #1: Rector is not changing processed PHP file, but changes corresponding template file (adds {varType} for each variable).
```
<?php

namespace Rector\Nette\Tests\Rector\Class_\LatteVarTypesBasedOnPresenterTemplateParametersRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'My title';
        $this->template->count = 123;
    }
}

?>
-----
-----
templates/Some/default.latte
-----
<h1>{$title}</h1>
<span class="count">{$count}</span>
-----
{varType string $title}
{varType int $count}

<h1>{$title}</h1>
<span class="count">{$count}</span>
```

Example #2: Rector creates Template class, uses it in processed PHP file, and also in corresponding template file (adds {templateType}).
```
<?php

namespace Rector\Nette\Tests\Rector\Class_\LatteVarTypesBasedOnPresenterTemplateParametersRector\Fixture;

use Nette\Application\UI\Presenter;

class SomePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'My title';
        $this->template->count = 123;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Class_\LatteVarTypesBasedOnPresenterTemplateParametersRector\Fixture;

use Nette\Application\UI\Presenter;

/**
 * @property-read SomeTemplate $template
 */
class SomePresenter extends Presenter
{
    public function renderDefault(): void
    {
        $this->template->title = 'My title';
        $this->template->count = 123;
    }
}

?>
-----
SomeTemplate.php
-----
-----
<?php

namespace Rector\Nette\Tests\Rector\Class_\LatteVarTypesBasedOnPresenterTemplateParametersRector\Fixture;

use Nette\Bridges\ApplicationLatte\Template;

class SomeTemplate extends Template
{
    public string $title;
    public int $count;
}
-----
templates/Some/default.latte
-----
<h1>{$title}</h1>
<span class="count">{$count}</span>
-----
{templateType \Rector\Nette\Tests\Rector\Class_\LatteVarTypesBasedOnPresenterTemplateParametersRector\Fixture\SomeTemplate}

<h1>{$title}</h1>
<span class="count">{$count}</span>
```
