<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SecurityBundle\DependencyInjection\Compiler;

/**
 * Adds all services with the tags "fxp_security.permission_loader" as arguments
 * of the "fxp_security.permission_loader.chain" service.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionLoaderPass extends AbstractLoaderPass
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('fxp_security.permission_loader.chain', 'fxp_security.permission_loader');
    }
}
