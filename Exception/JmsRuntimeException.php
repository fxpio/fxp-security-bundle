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

use JMS\SecurityExtraBundle\Exception\RuntimeException;

/**
 * Base JmsRuntimeException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class JmsRuntimeException extends RuntimeException implements ExceptionInterface
{
}
