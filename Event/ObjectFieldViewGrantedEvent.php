<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Event;

use Symfony\Component\Security\Acl\Voter\FieldVote;

/**
 * The object field view granted event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFieldViewGrantedEvent extends AbstractViewGrantedEvent
{
    /**
     * @var FieldVote
     */
    protected $fieldVote;

    /**
     * Constructor.
     *
     * @param FieldVote $fieldVote The ACL field vote
     */
    public function __construct(FieldVote $fieldVote)
    {
        parent::__construct($fieldVote->getDomainObject());

        $this->fieldVote = $fieldVote;
    }

    /**
     * Get the ACL field vote.
     *
     * @return FieldVote
     */
    public function getFieldVote()
    {
        return $this->fieldVote;
    }
}
