<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\VpsServer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VpsServerVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof VpsServer;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var VpsServer $vpsServer */
        $vpsServer = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($vpsServer, $user),
            self::EDIT => $this->canEdit($vpsServer, $user),
            self::DELETE => $this->canDelete($vpsServer, $user),
            default => false,
        };
    }

    private function canView(VpsServer $vpsServer, User $user): bool
    {
        return $vpsServer->getUser() === $user;
    }

    private function canEdit(VpsServer $vpsServer, User $user): bool
    {
        return $vpsServer->getUser() === $user;
    }

    private function canDelete(VpsServer $vpsServer, User $user): bool
    {
        return $vpsServer->getUser() === $user;
    }
}
