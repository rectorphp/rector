<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

final class GetterType
{
    /**
     * @Serializer\Type("string")
     */
    private $email;

    /**
     * @Serializer\Type("string")
     */
    private $password;

    /**
     * @Serializer\Type("string")
     */
    private $language;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function hasLanguage(): bool
    {
        return $this->language !== null;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use JMS\Serializer\Annotation as Serializer;

final class GetterType
{
    /**
     * @Serializer\Type("string")
     * @var string|null
     */
    private $email;

    /**
     * @Serializer\Type("string")
     * @var string|null
     */
    private $password;

    /**
     * @Serializer\Type("string")
     * @var string|null
     */
    private $language;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function hasLanguage(): bool
    {
        return $this->language !== null;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}

?>
