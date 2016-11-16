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

/**
 * Security extension tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSecurityExtensionTest extends AbstractSecurityExtensionTest
{
    public function testExtensionExist()
    {
        $container = $this->createContainer();
        $this->assertTrue($container->hasExtension('sonatra_security'));
    }
}
