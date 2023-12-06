<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Workflow\Workflow;

class WorkflowCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        #[TaggedIterator('workflow')] protected iterable $workflows,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function warmUp(): void
    {
        /** @var Workflow $workflow */
        foreach ($this->workflows as $workflow) {
            $workflowName = $workflow->getName();
            $transitions = $workflow->getDefinition()->getTransitions();

            foreach ($transitions as $transition) {
                $role = strtoupper('ROLE_' . $workflowName . '_WF_' . $transition->getName());
                
                $this->checkAndCreateCredential(
                    $role,
                    null,
                    $role,
                    0
                );
            }
        }
    }
}