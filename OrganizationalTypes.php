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
final class OrganizationalTypes
{
    /**
     * The OrganizationalTypes::OPTIONAL_FILTER_ALL type check if the filter must filter the list with objects linked
     * with an organization and without organization.
     *
     * @var string
     */
    const OPTIONAL_FILTER_ALL = 'all';

    /**
     * The OrganizationalTypes::OPTIONAL_FILTER_WITH_ORG type check if the filter must filter the list with objects linked
     * with an organization only.
     *
     * @var string
     */
    const OPTIONAL_FILTER_WITH_ORG = 'with_org';

    /**
     * The OrganizationalTypes::OPTIONAL_FILTER_WITHOUT_ORG type check if the filter must filter the list with objects linked
     * only without organization.
     *
     * @var string
     */
    const OPTIONAL_FILTER_WITHOUT_ORG = 'without_org';
}
