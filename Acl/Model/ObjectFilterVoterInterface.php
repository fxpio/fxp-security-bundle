<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Model;

/**
 * Acl Object Filter Voter Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface ObjectFilterVoterInterface
{
    /**
     * Check if the value is supported by this voter.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function supports($value);

    /**
     * Get the replacement value.
     *
     * @param mixed $value
     *
     * @return mixed The new value
     */
    public function getValue($value);
}
