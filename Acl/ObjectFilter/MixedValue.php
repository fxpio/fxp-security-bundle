<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\ObjectFilter;

use Sonatra\Bundle\SecurityBundle\Acl\Model\ObjectFilterVoterInterface;

/**
 * The Mixed Value Object Filter Voter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MixedValue implements ObjectFilterVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($value)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        return null;
    }
}
