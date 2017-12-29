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

use Fxp\Bundle\SecurityBundle\DependencyInjection\AccessControlConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Access Control Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AccessControlConfigurationTest extends TestCase
{
    public function testNoConfig()
    {
        $config = array();

        $processor = new Processor();
        $configuration = new AccessControlConfiguration(array(), array());
        $this->assertCount(1, $processor->processConfiguration($configuration, array($config)));
    }

    public function testConfig()
    {
        $config = array(
            'access_control' => array(
                array(
                    'ips' => '127.0.0.1',
                    'methods' => 'GET,POST',
                    'roles' => 'ROLE_USER,ROLE_ADMIN',
                ),
            ),
        );
        $validConfig = array(
            'access_control' => array(
                array(
                    'methods' => array('GET', 'POST'),
                    'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
                    'ips' => array('127.0.0.1'),
                    'requires_channel' => null,
                    'path' => null,
                    'host' => null,
                    'allow_if' => null,
                ),
            ),
        );

        $processor = new Processor();
        $configuration = new AccessControlConfiguration(array(), array());
        $fConfig = $processor->processConfiguration($configuration, array($config));

        $this->assertEquals($validConfig, $fConfig);
    }
}
