<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Lle\CruditBundle\Contracts\BrickConfigInterface;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Dto\Field\Field;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class CruditCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        #[TaggedIterator('crudit.config')] protected iterable $cruditConfigs,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function warmUp(): void
    {
        $i = 0;
        $keys = [
            CrudConfigInterface::INDEX,
            CrudConfigInterface::SHOW,
            CrudConfigInterface::EDIT,
            CrudConfigInterface::NEW,
            CrudConfigInterface::DELETE,
            CrudConfigInterface::EXPORT,
        ];
        foreach ($this->cruditConfigs as $cruditConfig) {
            /** @var CrudConfigInterface $cruditConfig */
            $rubrique = $cruditConfig->getName();

            // Page Roles
            foreach ($keys as $key) {
                $this->checkAndCreateCredential(
                    'ROLE_' . $cruditConfig->getName() . '_' . $key,
                    $rubrique,
                    $cruditConfig->getName() . $key,
                    $i++
                );

                // Field Roles
                foreach ($cruditConfig->getFields($key) as $field) {
                    $fields = $field instanceof Field ? [$field] : $field;

                    foreach ($fields as $subField) {
                        if ($subField->getRole()) {
                            $this->checkAndCreateCredential(
                                $subField->getRole(),
                                $rubrique,
                                $key . "/" . strtolower(str_replace("ROLE_${rubrique}_", "", $subField->getRole())),
                                $i++
                            );
                        }
                    }
                }
            }
        
            // Actions Roles
            foreach ($cruditConfig->getListActions() as $action) {
                if ($action->getPath()->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getPath()->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        $i++
                    );
                }

                if ($action->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        $i++
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
                        $i++
                    );
                }

                if ($action->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        $i++
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
                        $i++
                    );
                }

                if ($action->getRole()) {
                    $this->checkAndCreateCredential(
                        $action->getRole(),
                        $rubrique,
                        $action->getLabel(),
                        $i++
                    );
                }
            }

            // Tabs Roles
            foreach ($cruditConfig->getTabs() as $key => $brickConfig) {
                $brickConfigList = $brickConfig instanceof BrickConfigInterface ? [$brickConfig] : $brickConfig;

                foreach ($brickConfigList as $brickConfig) {
                    if ($brickConfig->getRole()) {
                        $this->checkAndCreateCredential(
                            $brickConfig->getRole(),
                            $rubrique,
                            $key . "/" . strtolower(str_replace("ROLE_${rubrique}_", "", $brickConfig->getRole())),
                            $i++
                        );
                    }
                }
            }
        }
    }
}
