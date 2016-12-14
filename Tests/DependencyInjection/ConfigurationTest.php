<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\DependencyInjection;

use Sonatra\Bundle\SecurityBundle\DependencyInjection\Configuration;
use Sonatra\Component\Security\SharingVisibilities;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockSharing;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testNoConfig()
    {
        $config = array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
        );

        $processor = new Processor();
        $configuration = new Configuration(array(), array());
        $processor->processConfiguration($configuration, array($config));
    }

    public function testPermissionConfigNormalization()
    {
        $config = array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                \stdClass::class => true,
            ),
        );

        $processor = new Processor();
        $configuration = new Configuration(array(), array());
        $res = $processor->processConfiguration($configuration, array($config));

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
    }

    public function testPermissionFieldOperationNormalization()
    {
        $operations = array(
            'read',
            'edit',
        );
        $config = array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                MockObject::class => array(
                    'fields' => array(
                        'name' => $operations,
                    ),
                ),
            ),
        );

        $processor = new Processor();
        $configuration = new Configuration(array(), array());
        $res = $processor->processConfiguration($configuration, array($config));

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
        $config = array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'permissions' => array(
                \stdClass::class => array(
                    'master_mapping_permissions' => array(
                        'view' => 'read',
                        'update' => 'edit',
                    ),
                ),
            ),
        );

        $processor = new Processor();
        $configuration = new Configuration(array(), array());
        $res = $processor->processConfiguration($configuration, array($config));

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
        $this->assertArrayHasKey('master_mapping_permissions', $res['permissions'][\stdClass::class]);
        $this->assertArrayHasKey('view', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
        $this->assertArrayHasKey('update', $res['permissions'][\stdClass::class]['master_mapping_permissions']);
    }

    public function testSharingSubjectConfigNormalization()
    {
        $config = array(
            'role_class' => MockRole::class,
            'permission_class' => MockPermission::class,
            'sharing_class' => MockSharing::class,
            'sharing' => array(
                'subjects' => array(
                    \stdClass::class => SharingVisibilities::TYPE_PRIVATE,
                ),
            ),
        );

        $processor = new Processor();
        $configuration = new Configuration(array(), array());
        $res = $processor->processConfiguration($configuration, array($config));

        $this->assertArrayHasKey('sharing', $res);
        $this->assertArrayHasKey('subjects', $res['sharing']);
        $this->assertArrayHasKey(\stdClass::class, $res['sharing']['subjects']);
    }
}
