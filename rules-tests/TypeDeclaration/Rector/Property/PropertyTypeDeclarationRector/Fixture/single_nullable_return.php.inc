<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Symfony\Component\HttpFoundation\File\File;

final class FileUpload
{
    private $file;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Symfony\Component\HttpFoundation\File\File;

final class FileUpload
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\File|null
     */
    private $file;

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }
}

?>
