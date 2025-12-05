<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class CruditCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        #[AutowireIterator('crudit.config')] protected iterable $cruditConfigs,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
        protected CredentialFactory $credentialFactory,
    ) {
    }

    public function warmUp(): void
    {
        /** @var CrudConfigInterface $cruditConfig */
        foreach ($this->cruditConfigs as $cruditConfig) {
            /** @var string $rubrique */
            $rubrique = $cruditConfig->getName();

            foreach (CrudConfigInterface::BASIC_ACTIONS_KEYS as $role => $label) {
                $this->createRoleForAction($cruditConfig, $role, $rubrique, $label);
            }

            // Page Roles
            foreach (CrudConfigInterface::BASIC_FIELDS_KEYS as $key) {
                $this->createRoleForFields($cruditConfig, $key, $rubrique);
            }

            foreach ($cruditConfig::ADDITIONAL_FIELDS_KEYS as $key) {
                $this->createRoleForFields($cruditConfig, $key, $rubrique);
            }

            // Actions Roles
            foreach ($cruditConfig->getListActions() as $action) {
                if ($action->getPath()->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getPath()->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        type: $action->isBatch() ? 'credential.action_batch.list' : 'credential.action.list',
                    );
                }

                if ($action->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        type: $action->isBatch() ? 'credential.action_batch.list' : 'credential.action.list',
                    );
                }
            }

            // Item Actions Roles
            foreach ($cruditConfig->getItemActions() as $action) {
                if ($action->getPath()->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getPath()->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        type: 'credential.action.item',
                    );
                }

                if ($action->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        type: 'credential.action.item',
                    );
                }
            }

            // Show Actions Roles
            foreach ($cruditConfig->getShowActions() as $action) {
                if ($action->getPath()->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getPath()->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        type: 'credential.action.show',
                    );
                }

                if ($action->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        type: 'credential.action.show',
                    );
                }
            }

            // Tabs Roles
            if ($cruditConfig->getTabConfig()?->getTabs()) {
                foreach ($cruditConfig->getTabConfig()->getTabs() as $tabs) {
                    if ($tabs->getRole()) {
                        $this->checkAndCreateCredential(
                            $tabs->getRole(),
                            $rubrique,
                            $tabs->getLabel(),
                            type: 'credential.tab',
                        );
                    }

                    foreach ($tabs->getBricks() as $brick) {
                        if ($brick->getRole()) {
                            /** @var class-string $brickClass */
                            $brickClass = get_class($brick);
                            $brickClassPart = explode('\\', $brickClass);

                            $this->checkAndCreateCredential(
                                $brick->getRole(),
                                $rubrique,
                                $tabs->getLabel(),
                                type: $brick->getTitle() ?? (
                                    'credential.'
                                    . strtolower(str_replace('Config', '', end($brickClassPart)))
                                ),
                            );
                        }
                    }
                }
            }
        }
    }

    public function createRoleForAction(
        CrudConfigInterface $crudConfig,
        string $role,
        string $rubrique,
        string $label,
    ): void {
        $this->checkAndCreateCredential(
            strtoupper('ROLE_' . $crudConfig->getName() . '_' . $role),
            $rubrique,
            strtolower('action.' . $label),
            type: 'credential.action',
        );
    }

    public function createRoleForFields(CrudConfigInterface $cruditConfig, string $key, string $rubrique): void
    {
        foreach ($cruditConfig->getFields($key) as $field) {
            $fields = $field instanceof Field ? [$field] : $field;

            foreach ($fields as $subField) {
                if ($subField->getRole()) {
                    $this->checkAndCreateCredential(
                        $subField->getRole(),
                        $rubrique,
                        $subField->getLabel(),
                        type: 'credential.field',
                    );
                }
            }
        }
    }
}
