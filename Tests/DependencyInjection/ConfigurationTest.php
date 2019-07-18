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
        static::assertCount(13, $processor->processConfiguration($configuration, [$config]));
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

        static::assertArrayHasKey('permissions', $res);
        static::assertArrayHasKey(\stdClass::class, $res['permissions']);
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

        static::assertArrayHasKey('permissions', $res);
        static::assertArrayHasKey(MockObject::class, $res['permissions']);
        static::assertArrayHasKey('master_mapping_permissions', $res['permissions'][MockObject::class]);

        $cConf = $res['permissions'][MockObject::class];

        static::assertArrayHasKey('fields', $cConf);
        static::assertArrayHasKey('name', $cConf['fields']);
        static::assertArrayHasKey('operations', $cConf['fields']['name']);
        static::assertSame($operations, $cConf['fields']['name']['operations']);
        static::assertNull($cConf['fields']['name']['editable']);
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

        static::assertArrayHasKey('permissions', $res);
        static::assertArrayHasKey(\stdClass::class, $res['permissions']);
        static::assertArrayHasKey('master_mapping_permissions', $res['permissions'][\stdClass::class]);
        static::assertArrayHasKey('view', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
        static::assertArrayHasKey('update', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
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

        static::assertArrayHasKey('sharing', $res);
        static::assertArrayHasKey('subjects', $res['sharing']);
        static::assertArrayHasKey(\stdClass::class, $res['sharing']['subjects']);
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

        static::assertArrayHasKey('object_filter', $res);
        static::assertArrayHasKey('excluded_classes', $res['object_filter']);
        static::assertSame($expected, $res['object_filter']['excluded_classes']);
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

        static::assertArrayHasKey('object_filter', $res);
        static::assertArrayHasKey('excluded_classes', $res['object_filter']);
        static::assertSame($expected, $res['object_filter']['excluded_classes']);
    }

    public function testAnnotationConfig(): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $res = $processor->processConfiguration($configuration, []);

        static::assertArrayHasKey('annotations', $res);
        static::assertArrayHasKey('include_paths', $res['annotations']);
        static::assertSame(['%kernel.project_dir%/src'], $res['annotations']['include_paths']);
    }
}
