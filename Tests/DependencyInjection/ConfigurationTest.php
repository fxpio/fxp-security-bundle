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
use Sonatra\Component\Security\SharingTypes;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
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
                \stdClass::class => SharingTypes::TYPE_PRIVATE,
            ),
        );

        $processor = new Processor();
        $configuration = new Configuration(array(), array());
        $res = $processor->processConfiguration($configuration, array($config));

        $this->assertArrayHasKey('permissions', $res);
        $this->assertArrayHasKey(\stdClass::class, $res['permissions']);
        $this->assertArrayHasKey('sharing', $res['permissions'][\stdClass::class]);
        $this->assertSame(SharingTypes::TYPE_PRIVATE, $res['permissions'][\stdClass::class]['sharing']);
    }
}
