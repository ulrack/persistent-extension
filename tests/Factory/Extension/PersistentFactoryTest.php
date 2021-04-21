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
use Ulrack\PersistentExtension\Factory\Extension\PersistentFactory;

/**
 * @coversDefaultClass \Ulrack\PersistentExtension\Factory\Extension\PersistentFactory
 */
class PersistentFactoryTest extends TestCase
{
    /**
     * @covers ::create
     * @covers ::__destruct
     *
     * @return void
     */
    public function testCreate(): void
    {
        $subject = new PersistentFactory();

        $serviceKey = 'foo';

        $resourceManager = $this->createMock(ResourceManagerInterface::class);
        $varFileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemDriver = $this->createMock(FileSystemDriverInterface::class);
        $persistentFileSystem = $this->createMock(FileSystemInterface::class);
        $fileSystemNormalizer = $this->createMock(FileSystemNormalizerInterface::class);
        $create = function (string $key) use ($resourceManager) {
            if ($key === 'internal.core.resource.manager') {
                return $resourceManager;
            }
        };

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

        $persistentFileSystem->expects(static::exactly(2))
            ->method('isFile')
            ->willReturnOnConsecutiveCalls(true, false);

        $fileSystemDriver->expects(static::exactly(2))
            ->method('getFileSystemNormalizer')
            ->willReturn($fileSystemNormalizer);

        $fileSystemNormalizer->expects(static::once())
            ->method('normalizeFromFile')
            ->willReturn(['bar' => 'baz']);

        $this->assertInstanceOf(
            StorageInterface::class,
            $subject->create(
                $serviceKey,
                ['bar' => 'baz'],
                $create
            )
        );
    }
}
