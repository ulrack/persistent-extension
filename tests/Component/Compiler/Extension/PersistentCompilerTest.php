<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\PersistentExtension\Tests\Component\Compiler\Extension;

use PHPUnit\Framework\TestCase;
use Ulrack\Services\Common\ServiceRegistryInterface;
use GrizzIt\Validator\Component\Logical\AlwaysValidator;
use Ulrack\PersistentExtension\Component\Compiler\Extension\PersistentCompiler;

/**
 * @coversDefaultClass \Ulrack\PersistentExtension\Component\Compiler\Extension\PersistentCompiler
 */
class PersistentCompilerTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::compile
     */
    public function testCompiler(): void
    {
        $services = [
            'persistent' => [
                'foo' => [
                    'my-default' => true
                ]
            ]
        ];

        $subject = new PersistentCompiler(
            $this->createMock(ServiceRegistryInterface::class),
            'persistent',
            new AlwaysValidator(true),
            [],
            [$this, 'getHooks']
        );

        $this->assertEquals($services, $subject->compile($services));
    }

    /**
     * Required method.
     *
     * @return array
     */
    public function getHooks(): array
    {
        return [];
    }
}
