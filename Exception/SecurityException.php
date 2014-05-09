<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Exception;

use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Base SecurityException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityException extends Exception implements ExceptionInterface
{
}
