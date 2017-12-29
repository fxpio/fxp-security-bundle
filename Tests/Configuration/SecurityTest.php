<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\Tests\Configuration;

use Fxp\Bundle\SecurityBundle\Configuration\Security;
use PHPUnit\Framework\TestCase;

/**
 * Security Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SecurityTest extends TestCase
{
    public function testSecurityAnnotation()
    {
        $exp = 'has_role("ROLE_USER")';
        $exp2 = 'has_role("ROLE_ADMIN")';
        $security = new Security([
            'expression' => $exp,
        ]);

        $this->assertSame('fxp_security', $security->getAliasName());
        $this->assertFalse($security->allowArray());
        $this->assertSame($exp, $security->getExpression());

        $security->setExpression($exp2);
        $this->assertSame($exp2, $security->getExpression());

        $security->setValue($exp);
        $this->assertSame($exp, $security->getExpression());
    }
}
