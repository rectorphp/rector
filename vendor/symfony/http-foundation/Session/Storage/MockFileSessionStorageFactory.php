<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210827\Symfony\Component\HttpFoundation\Session\Storage;

use RectorPrefix20210827\Symfony\Component\HttpFoundation\Request;
// Help opcache.preload discover always-needed symbols
\class_exists(\RectorPrefix20210827\Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage::class);
/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class MockFileSessionStorageFactory implements \RectorPrefix20210827\Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface
{
    private $savePath;
    private $name;
    private $metaBag;
    /**
     * @see MockFileSessionStorage constructor.
     */
    public function __construct(string $savePath = null, string $name = 'MOCKSESSID', \RectorPrefix20210827\Symfony\Component\HttpFoundation\Session\Storage\MetadataBag $metaBag = null)
    {
        $this->savePath = $savePath;
        $this->name = $name;
        $this->metaBag = $metaBag;
    }
    public function createStorage(?\RectorPrefix20210827\Symfony\Component\HttpFoundation\Request $request) : \RectorPrefix20210827\Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface
    {
        return new \RectorPrefix20210827\Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage($this->savePath, $this->name, $this->metaBag);
    }
}
