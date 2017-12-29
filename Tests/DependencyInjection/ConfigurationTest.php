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
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testNoConfig()
    {
        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
        ];

        $processor = new Processor();
        $configuration = new Configuration([], []);
        $this->assertCount(16, $processor->processConfiguration($configuration, [$config]));
    }

    public function testPermissionConfigNormalization()
    {
        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                \stdClass::class => true,
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration([], []);
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
    }

    public function testPermissionFieldOperationNormalization()
    {
        $operations = [
            'read',
            'edit',
        ];
        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => [
                MockObject::class => [
                    'fields' => [
                        'name' => $operations,
                    ],
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration([], []);
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(MockObject::class, $res['permissions']);
        $this->assertArrayHasKey('master_mapping_permissions', $res['permissions'][MockObject::class]);

        $cConf = $res['permissions'][MockObject::class];

        $this->assertArrayHasKey('fields', $cConf);
        $this->assertArrayHasKey('name', $cConf['fields']);
        $this->assertArrayHasKey('operations', $cConf['fields']['name']);
        $this->assertSame($operations, $cConf['fields']['name']['operations']);
    }

    public function testPermissionMasterFieldMapping()
    {
        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
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
        $configuration = new Configuration([], []);
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
        $this->assertArrayHasKey('master_mapping_permissions', $res['permissions'][\stdClass::class]);
        $this->assertArrayHasKey('view', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
        $this->assertArrayHasKey('update', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
    }

    public function testSharingSubjectConfigNormalization()
    {
        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => [
                'subjects' => [
                    \stdClass::class => SharingVisibilities::TYPE_PRIVATE,
                ],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration([], []);
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('sharing', $res);
        $this->assertArrayHasKey('subjects', $res['sharing']);
        $this->assertArrayHasKey(\stdClass::class, $res['sharing']['subjects']);
    }

    public function testObjectFilterConfigByDefault()
    {
        $expected = [
            PermissionInterface::class,
            SharingInterface::class,
        ];

        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
        ];

        $processor = new Processor();
        $configuration = new Configuration([], []);
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('object_filter', $res);
        $this->assertArrayHasKey('excluded_classes', $res['object_filter']);
        $this->assertSame($expected, $res['object_filter']['excluded_classes']);
    }

    public function testObjectFilterConfig()
    {
        $expected = [
            \stdClass::class,
        ];

        $config = [
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'object_filter' => [
                'excluded_classes' => $expected,
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration([], []);
        $res = $processor->processConfiguration($configuration, [$config]);

        $this->assertArrayHasKey('object_filter', $res);
        $this->assertArrayHasKey('excluded_classes', $res['object_filter']);
        $this->assertSame($expected, $res['object_filter']['excluded_classes']);
    }
}
