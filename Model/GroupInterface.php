<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Model;

use FOS\UserBundle\Model\GroupInterface as BaseGroupInterface;

/**
 * User interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface GroupInterface extends BaseGroupInterface
{
    /**
     * Get the group name used by security.
     *
     * @return string
     */
    public function getGroup();
}
