<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source;

use Rector\Contract\Bridge\RepositoryForDoctrineEntityProviderInterface;

final class RepositoryForDoctrineEntityProvider implements RepositoryForDoctrineEntityProviderInterface
{
    /**
     * @var string[]
     */
    private $map = [
        'AppBundle\Entity\Post' => 'AppBundle\Repository\PostRepository',
    ];

    public function provideRepositoryForEntity(string $name): ?string
    {
        if ($this->isAlias($name)) {
            return $this->resoleFromAlias($name);
        }

        return $this->map[$name] ?? null;
    }

    private function isAlias(string $name): bool
    {
        return strpos($name, ':') !== false;
    }

    private function resoleFromAlias(string $name): ?string
    {
        [$namespaceAlias, $simpleClassName] = explode(':', $name, 2);

        $pattern = sprintf('/(%s{1}.*%s)/', $namespaceAlias, $simpleClassName);

        foreach ($this->map as $key => $value) {
            if (preg_match($pattern, $key)) {
                return $value;
            }
        }

        return null;
    }
}
