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
 *
 * @internal
 */
final class SecurityTest extends TestCase
{
    public function testSecurityAnnotation(): void
    {
        $exp = 'has_role("ROLE_USER")';
        $exp2 = 'has_role("ROLE_ADMIN")';
        $security = new Security([
            'expression' => $exp,
        ]);

        $this->assertSame('fxp_security', $security->getAliasName());
        $this->assertTrue($security->allowArray());
        $this->assertFalse($security->isOverriding());
        $this->assertSame($exp, $security->getExpression());
        $this->assertNull($security->getStatusCode());
        $this->assertNull($security->getMessage());

        $security->setOverride(true);
        $this->assertTrue($security->isOverriding());

        $security->setExpression($exp2);
        $this->assertSame($exp2, $security->getExpression());

        $security->setValue($exp);
        $this->assertSame($exp, $security->getExpression());

        $security->setStatusCode(401);
        $this->assertSame(401, $security->getStatusCode());

        $security->setMessage('Custom message');
        $this->assertSame('Custom message', $security->getMessage());
    }
}
