<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Listener;

/**
 * Interface for events of security identities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface EventStrategyIdentityInterface
{
    /**
     * Get the cache id for the event security identities.
     *
     * @return string
     */
    public function getCacheId();
}
