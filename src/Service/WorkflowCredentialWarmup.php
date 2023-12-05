<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Yaml\Yaml;

class WorkflowCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        protected KernelInterface $kernel,
        protected Registry $registry,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function warmUp(): void
    {
        $projectDir = $this->kernel->getProjectDir();

        if (is_file($projectDir . '/config/packages/workflow.yaml')) {
            $fileContent = Yaml::parseFile($projectDir . '/config/packages/workflow.yaml');

            if (
                array_key_exists('framework', $fileContent)
                && array_key_exists('workflows', $fileContent['framework'])
            ) {
                foreach ($fileContent['framework']['workflows'] as $workflowName => $workflowConfig) {
                    if (array_key_exists('supports', $workflowConfig)) {
                        foreach ($workflowConfig['supports'] as $support) {
                            $this->generateRoleForEntity($workflowName, $support);
                        }
                    }
                }
            }
        }
    }

    public function generateRoleForEntity(string $workflowName, string $support): void
    {
        $transitions = $this->getTransitionsForEntity($workflowName, $support);

        foreach ($transitions as $transition) {
            $role = strtoupper('ROLE_' . $workflowName . '_WF_' . $transition->getName());

            $this->checkAndCreateCredential(
                $role,
                strtoupper(str_replace('App\Entity\\', '', $support)),
                $role,
                0
            );
        }
    }

    public function getTransitionsForEntity(string $workflowName, string $support): array
    {
        if (class_exists($support)) {
            $entity = new $support();

            $workflow = $this->registry->get($entity, $workflowName);
            $transitions = $workflow->getDefinition()->getTransitions();

            return $transitions;
        }

        return [];
    }
}