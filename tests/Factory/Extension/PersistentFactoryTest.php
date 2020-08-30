<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\PersistentExtension\Tests\Factory\Extension;

use PHPUnit\Framework\TestCase;
use GrizzIt\Vfs\Common\FileSystemInterface;
use GrizzIt\Storage\Common\StorageInterface;
use GrizzIt\Vfs\Common\FileSystemDriverInterface;
use GrizzIt\Vfs\Common\FileSystemNormalizerInterface;
use Ulrack\Kernel\Common\Manager\ResourceManagerInterface;
use Ulrack\Services\Exception\DefinitionNotFoundException;
use Ulrack\PersistentExtension\Factory\Extension\PersistentFactory;

/**
 * @coversDefaultClass \Ulrack\PersistentExtension\Factory\Extension\PersistentFactory
 */
class PersistentFactoryTest extends TestCase
{
    /**
     * @covers ::create
     * @covers ::registerService
     * @covers ::createStorage
     * @covers ::__destruct
     *
     * @return void
     */
    public function testCreate(): void
    {
        $subject = $this->createPartialMock(
            PersistentFactory::class,
            [
                'preCreate',
                'getParameters',
                'getKey',
                'getServices',
                'getInternalService',
                'superCreate',
                'postCreate'
            ]
        );

        $serviceKey = 'foo';

        $subject->expects(static::once())
            ->method('preCreate')
            ->willReturn(['serviceKey' => $serviceKey]);

        $subject->registerService('foo', ['bar' => 'baz']);

        $subject->expects(static::once())
            ->method('getServices')
            ->willReturn(['persistent' => ['foo' => ['bar' => 'baz']]]);

        $subject->method('getKey')
            ->willReturn('persistent');

        $resourceManager = $this->createMock(ResourceManagerInterface::class);
        $varFileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemDriver = $this->createMock(FileSystemDriverInterface::class);
        $persistentFileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(FileSystemNormalizerInterface::class);

        $subject->expects(static::once())
            ->method('superCreate')
            ->with('services.core.resource.manager')
            ->willReturn($resourceManager);

        $subject->expects(static::once())
            ->method('postCreate')
            ->willReturnCallback(
                function (
                    string $serviceKey,
                    StorageInterface $storage,
                    array $parameters
                ): array {
                    return [
                        'serviceKey' => $serviceKey,
                        'return' => $storage,
                        'parameters' => $parameters
                    ];
                }
            );

        $resourceManager->expects(static::once())
            ->method('getVarFileSystem')
            ->willReturn($varFileSystem);

        $varFileSystem->expects(static::once())
            ->method('isDirectory')
            ->willReturn(false);

        $resourceManager->method('getFileSystemDriver')
            ->willReturn($fileSystemDriver);

        $fileSystemDriver->expects(static::once())
            ->method('connect')
            ->willReturn($persistentFileSystem);

        $persistentFileSystem->expects(static::once())
            ->method('isFile')
            ->willReturn(true);

        $fileSystemDriver->expects(static::once())
            ->method('getFileSystemNormalizer')
            ->willReturn($fileSystemNormalizer);

        $fileSystemNormalizer->expects(static::once())
            ->method('normalizeFromFile')
            ->willReturn(['bar' => 'baz']);

        $this->assertInstanceOf(StorageInterface::class, $subject->create($serviceKey));
    }

    /**
     * @covers ::create
     * @covers ::registerService
     * @covers ::createStorage
     * @covers ::__destruct
     *
     * @return void
     */
    public function testCreateFail(): void
    {
        $subject = $this->createPartialMock(
            PersistentFactory::class,
            [
                'preCreate',
                'getParameters',
                'getKey',
                'getServices',
                'getInternalService',
                'superCreate',
                'postCreate'
            ]
        );

        $serviceKey = 'foo';

        $subject->expects(static::once())
            ->method('preCreate')
            ->willReturn(['serviceKey' => $serviceKey]);

        $subject->method('getKey')
            ->willReturn('persistent');

        $subject->expects(static::once())
            ->method('getServices')
            ->willReturn(['persistent' => ['bar' => ['bar' => 'baz']]]);

        $this->expectException(DefinitionNotFoundException::class);

        $subject->create($serviceKey);
    }
}
