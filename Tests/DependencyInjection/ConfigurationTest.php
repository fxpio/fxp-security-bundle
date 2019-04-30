<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\DependencyInjection;

use Fxp\Bundle\SecurityBundle\DependencyInjection\Configuration;
use Fxp\Component\Security\Model\PermissionInterface;
use Fxp\Component\Security\Model\SharingInterface;
use Fxp\Component\Security\SharingVisibilities;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ConfigurationTest extends TestCase
{
    public function testNoConfig(): void
    {
        $config = [];
        $processor = new Processor();
        $configuration = new Configuration();
        $this->assertCount(13, $processor->processConfiguration($configuration, [$config]));
    }

    public function testPermissionConfigNormalization(): void
    {
        $config = [
            'permissions' => [
                \stdClass::class => true,
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
    }

    public function testPermissionFieldOperationNormalization(): void
    {
        $operations = [
            'read',
            'edit',
        ];
        $config = [
            'permissions' => [
                MockObject::class => [
                    'fields' => [
                        'name' => $operations,
                    ],
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(MockObject::class, $res['permissions']);
        $this->assertArrayHasKey('master_mapping_permissions', $res['permissions'][MockObject::class]);

        $cConf = $res['permissions'][MockObject::class];

        $this->assertArrayHasKey('fields', $cConf);
        $this->assertArrayHasKey('name', $cConf['fields']);
        $this->assertArrayHasKey('operations', $cConf['fields']['name']);
        $this->assertSame($operations, $cConf['fields']['name']['operations']);
        $this->assertNull($cConf['fields']['name']['editable']);
    }

    public function testPermissionMasterFieldMapping(): void
    {
        $config = [
            'permissions' => [
                \stdClass::class => [
                    'master_mapping_permissions' => [
                        'view' => 'read',
                        'update' => 'edit',
                    ],
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
        $this->assertArrayHasKey('master_mapping_permissions', $res['permissions'][\stdClass::class]);
        $this->assertArrayHasKey('view', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
        $this->assertArrayHasKey('update', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
    }

    public function testSharingSubjectConfigNormalization(): void
    {
        $config = [
            'sharing' => [
                'subjects' => [
                    \stdClass::class => SharingVisibilities::TYPE_PRIVATE,
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('sharing', $res);
        $this->assertArrayHasKey('subjects', $res['sharing']);
        $this->assertArrayHasKey(\stdClass::class, $res['sharing']['subjects']);
    }

    public function testObjectFilterConfigByDefault(): void
    {
        $expected = [
            PermissionInterface::class,
            SharingInterface::class,
        ];

        $config = [];
        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('object_filter', $res);
        $this->assertArrayHasKey('excluded_classes', $res['object_filter']);
        $this->assertSame($expected, $res['object_filter']['excluded_classes']);
    }

    public function testObjectFilterConfig(): void
    {
        $expected = [
            \stdClass::class,
        ];

        $config = [
            'object_filter' => [
                'excluded_classes' => $expected,
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('object_filter', $res);
        $this->assertArrayHasKey('excluded_classes', $res['object_filter']);
        $this->assertSame($expected, $res['object_filter']['excluded_classes']);
    }
}
