<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Tests\Configuration;

use Sonatra\Bundle\SecurityBundle\Configuration\Security;

/**
 * Security Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityTest extends \PHPUnit_Framework_TestCase
{
    public function testSecurityAnnotation()
    {
        $exp = 'has_role("ROLE_USER")';
        $exp2 = 'has_role("ROLE_ADMIN")';
        $security = new Security(array(
            'expression' => $exp,
        ));

        $this->assertSame('sonatra_security', $security->getAliasName());
        $this->assertFalse($security->allowArray());
        $this->assertSame($exp, $security->getExpression());

        $security->setExpression($exp2);
        $this->assertSame($exp2, $security->getExpression());

        $security->setValue($exp);
        $this->assertSame($exp, $security->getExpression());
    }
}
