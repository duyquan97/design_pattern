<?php

namespace App\Security\Voter;

use App\Entity\Partner;
use App\Service\ChannelManager\ChannelManagerList;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class BB8Voter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BB8Voter extends PartnerVoter
{
    const BB8_OPERATION = "bb8_operation";

    /**
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (self::BB8_OPERATION !== $attribute) {
            return false;
        }

        if (!$subject instanceof Partner) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param string         $attribute
     * @param Partner        $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $isCorrectUser = parent::voteOnAttribute($attribute, $subject, $token);

        return $subject->getChannelManager()->getIdentifier() === ChannelManagerList::BB8 && $isCorrectUser;
    }
}
