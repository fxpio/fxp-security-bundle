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

use Sonatra\Bundle\SecurityBundle\Exception\SecurityException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class related functionality for acl manipulation.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclUtils
{
    /**
     * Convert the acl name or the array of acl name to mask.
     *
     * @param int | string | array $mask
     *
     * @return integer
     */
    public static function convertToMask($mask)
    {
        if (is_int($mask)) {
            return $mask;
        }

        if (!is_string($mask) && !is_array($mask)) {
            throw new SecurityException('The mask must be a string, or array of string or int (the symfony mask value)');
        }

        // convert the rights to mask
        $mask = (array) $mask;
        $builder = new MaskBuilder();
        $maskConverted = null;

        try {
            foreach ($mask as $m) {
                $maskConverted = strtoupper($m);
                $builder->add($m);
            }

        } catch (\Exception $e) {
            throw new SecurityException(sprintf('The right "%s" does not exist', $maskConverted));
        }

        return $builder->get();
    }

    /**
     * Convert the mask to array of acl name.
     *
     * @param int The mask
     *
     * @return array The list of permission (in string)
     */
    public static function convertToAclName($mask)
    {
        $mb = new MaskBuilder($mask);
        $pattern = $mb->getPattern();
        $rights = array();

        if (false !== strpos($pattern, MaskBuilder::CODE_VIEW)) {
            $rights[] = 'VIEW';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_CREATE)) {
            $rights[] = 'CREATE';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_EDIT)) {
            $rights[] = 'EDIT';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_DELETE)) {
            $rights[] = 'DELETE';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_UNDELETE)) {
            $rights[] = 'UNDELETE';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_OPERATOR)) {
            $rights[] = 'OPERATOR';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_MASTER)) {
            $rights[] = 'MASTER';
        }

        if (false !== strpos($pattern, MaskBuilder::CODE_OWNER)) {
            $rights[] = 'OWNER';
        }

        return $rights;
    }
}
