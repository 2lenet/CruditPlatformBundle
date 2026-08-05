<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;

class CredentialCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
        protected CredentialFactory $credentialFactory,
    ) {
    }

    public function warmUp(): void
    {
        $roles = [
            'ROLE_CREDENTIAL_ACTION_TOGGLEGROUP' => 'action.credential.toggle_group',
            'ROLE_CREDENTIAL_ACTION_TOGGLERUBRIQUE' => 'action.credential.toggle_rubrique',
            'ROLE_CREDENTIAL_ACTION_TOGGLECREDENTIAL' => 'action.credential.toggle_credential',
            'ROLE_CREDENTIAL_ACTION_ALLOWSTATUS' => 'action.credential.allow_status',
            'ROLE_CREDENTIAL_ACTION_UPDATE' => 'action.credential.update',
            'ROLE_CREDENTIAL_ACTION_REMOTEREPOSITORY' => 'action.credential.remote_repository',
        ];
        foreach ($roles as $role => $label) {
            echo "$role\n";
            $this->checkAndCreateCredential($role, 'CREDENTIAL', $label, type: 'credential.action');
        }
    }
}
