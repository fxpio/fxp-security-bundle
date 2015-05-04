<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Util;

use Sonatra\Bundle\SecurityBundle\Acl\Model\AclRuleManagerInterface;

/**
 * Util for acl rule.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AclRuleUtil
{
    /**
     * Disable the acl rules and return the previous status before disabling.
     *
     * @param AclRuleManagerInterface $aclRule The acl rule manager
     *
     * @return bool The previous value of enabled status
     */
    public static function disable(AclRuleManagerInterface $aclRule)
    {
        $previous = !$aclRule->isDisabled();
        $aclRule->disable();

        return $previous;
    }

    /**
     * Enable the acl rules only if the previous status is on enabled.
     *
     * @param AclRuleManagerInterface $aclRule  The acl rule manager
     * @param bool                    $previous The previous status of acl rule enabled
     */
    public static function enable(AclRuleManagerInterface $aclRule, $previous = true)
    {
        if ($previous) {
            $aclRule->enable();
        }
    }
}
