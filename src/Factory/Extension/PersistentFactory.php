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
use Ulrack\Services\Exception\DefinitionNotFoundException;
use Ulrack\Services\Common\AbstractServiceFactoryExtension;

class PersistentFactory extends AbstractServiceFactoryExtension
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
    private $resourceManager;

    /**
     * Contains the persistent file system.
     *
     * @var FileSystemInterface
     */
    private $persistentFileSystem;

    /**
     * Contains the open storages.
     *
     * @var StorageInterface[]
     */
    private $openStorages = [];

    /**
     * Register a value to a service key.
     *
     * @param string $serviceKey
     * @param mixed $value
     *
     * @return void
     */
    public function registerService(string $serviceKey, $value): void
    {
        $this->services[$serviceKey] = $value;
    }

    /**
     * Invoke the invocation and return the result.
     *
     * @param string $serviceKey
     *
     * @return mixed
     *
     * @throws DefinitionNotFoundException When the definition can not be found.
     */
    public function create(string $serviceKey)
    {
        $serviceKey = $this->preCreate(
            $serviceKey,
            $this->getParameters()
        )['serviceKey'];

        $internalKey = preg_replace(
            sprintf('/^%s\\./', preg_quote($this->getKey())),
            '',
            $serviceKey,
            1
        );

        $services = $this->getServices()[$this->getKey()];
        if (!isset($services[$internalKey])) {
            throw new DefinitionNotFoundException($serviceKey);
        }

        return $this->postCreate(
            $serviceKey,
            $this->createStorage(
                $internalKey,
                $services[$internalKey]
            ),
            $this->getParameters()
        )['return'];
    }

    /**
     * Creates a storage.
     *
     * @return StorageInterface
     */
    public function createStorage(string $key, $value): StorageInterface
    {
        $fileName = $key . '.json';
        if (!isset($this->openStorages[$fileName])) {
            if (is_null($this->resourceManager)) {
                /** @var ResourceManagerInterface $resourceManager */
                $this->resourceManager = $this->superCreate(
                    'services.core.resource.manager'
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

            $fileValue = $value;
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
