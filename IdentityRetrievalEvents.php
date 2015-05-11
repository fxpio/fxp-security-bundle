<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class IdentityRetrievalEvents
{
    /**
     * The IdentityRetrievalEvents::PRE event occurs before the retrieval of
     * all security identities.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent instance.
     *
     * @var string
     */
    const PRE = 'sonatra_security.security_identity_retrieval_strategy.pre';

    /**
     * The IdentityRetrievalEvents::ADD event occurs when the security
     * identities are adding.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent instance.
     *
     * @var string
     */
    const ADD = 'sonatra_security.security_identity_retrieval_strategy.add';

    /**
     * The IdentityRetrievalEvents::POST event occurs after the retrieval of
     * all security identities.
     *
     * The event listener method receives a
     * Sonatra\Bundle\SecurityBundle\Event\SecurityIdentityEvent instance.
     *
     * @var string
     */
    const POST = 'sonatra_security.security_identity_retrieval_strategy.post';
}
