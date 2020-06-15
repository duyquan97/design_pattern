<?php

namespace App\Security\Voter;

use App\Entity\Partner;
use App\Service\ChannelManager\ChannelManagerList;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class WubookVoter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WubookVoter extends PartnerVoter
{
    const WUBOOK_OPERATION = "wubook_operation";

    /**
     *
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (self::WUBOOK_OPERATION !== $attribute) {
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

        return ($subject->getChannelManager() && $subject->getChannelManager()->getIdentifier() === ChannelManagerList::WUBOOK) && $isCorrectUser;
    }
}
