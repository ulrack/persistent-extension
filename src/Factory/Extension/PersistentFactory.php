<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\PersistentExtension\Factory\Extension;

use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Storage\Common\StorageInterface;
use GrizzIt\Storage\Component\ObjectStorage;
use Ulrack\Kernel\Common\Manager\ResourceManagerInterface;
use GrizzIt\Services\Common\Factory\ServiceFactoryExtensionInterface;

class PersistentFactory implements ServiceFactoryExtensionInterface
{
    /**
     * Persistent directory path.
     */
    private const PERSISTENT_DIRECTORY = '/persistent';

    /**
     * Contains the resource manager.
     *
     * @var ResourceManagerInterface
     */
    private ?ResourceManagerInterface $resourceManager = null;

    /**
     * Contains the persistent file system.
     *
     * @var FileSystemInterface
     */
    private ?FileSystemInterface $persistentFileSystem = null;

    /**
     * Contains the open storages.
     *
     * @var StorageInterface[]
     */
    private array $openStorages = [];

    /**
     * Converts a service key and definition to an instance.
     *
     * @param string $key
     * @param mixed $definition
     * @param callable $create
     *
     * @return mixed
     */
    public function create(
        string $key,
        mixed $definition,
        callable $create
    ): mixed {
        $fileName = $key . '.json';
        if (!isset($this->openStorages[$fileName])) {
            if (is_null($this->resourceManager)) {
                /** @var ResourceManagerInterface $resourceManager */
                $this->resourceManager = $create(
                    'internal.core.resource.manager'
                );
            }

            if (is_null($this->persistentFileSystem)) {
                $varFileSystem = $this->resourceManager->getVarFileSystem();
                if (!$varFileSystem->isDirectory(self::PERSISTENT_DIRECTORY)) {
                    $varFileSystem->makeDirectory(self::PERSISTENT_DIRECTORY);
                }

                $this->persistentFileSystem = $this->resourceManager
                    ->getFileSystemDriver()
                    ->connect(
                        $varFileSystem->realpath(self::PERSISTENT_DIRECTORY)
                    );
            }

            $fileValue = $definition;
            if ($this->persistentFileSystem->isFile($fileName)) {
                $fileValue = $this->resourceManager
                    ->getFileSystemDriver()
                    ->getFileSystemNormalizer()
                    ->normalizeFromFile($this->persistentFileSystem, $fileName);
            }

            $this->openStorages[$fileName] = new ObjectStorage($fileValue);
        }

        return $this->openStorages[$fileName];
    }

    /**
     * Destructor.
     *
     * Updates all storages.
     */
    public function __destruct()
    {
        if (!is_null($this->resourceManager)) {
            $normalizer = $this->resourceManager
                ->getFileSystemDriver()
                ->getFileSystemNormalizer();
            foreach ($this->openStorages as $fileName => $storage) {
                if (!$this->persistentFileSystem->isFile($fileName)) {
                    $this->persistentFileSystem->touch($fileName);
                }

                $normalizer->denormalizeToFile(
                    $this->persistentFileSystem,
                    $fileName,
                    iterator_to_array($storage)
                );
            }
        }
    }
}
