<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Command\User;

use FOS\UserBundle\Command\PromoteUserCommand as BasePromoteUserCommand;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PromoteUserCommand extends BasePromoteUserCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('security:user:promote')
            ->setDescription($this->getDescription().' <comment>(fos:user:promote alias)</comment>');
    }
}
