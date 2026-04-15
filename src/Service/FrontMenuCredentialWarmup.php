<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Lle\CruditBundle\Registry\MenuRegistry;

class FrontMenuCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        protected MenuRegistry $menuRegistry,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
        protected CredentialFactory $credentialFactory,
    ) {
    }

    public function warmUp(): void
    {
        $rubrique = 'MENU';
        $i = 0;
        foreach ($this->menuRegistry->getElements('') as $menuItem) {
            /** @var class-string $class */
            $class = get_class($menuItem);

            if ($menuItem->getRole()) {
                echo("\n" . $menuItem->getRole());
                $this->checkAndCreateCredential(
                    $menuItem->getRole(),
                    $rubrique,
                    method_exists($class, 'getLibelle') ? $menuItem->getLibelle() : $menuItem->getId(),
                    type: 'credential.menu',
                );
            }

            foreach ($menuItem->getChildren() as $submenuItem) {
                /** @var class-string $class */
                $class = get_class($submenuItem);

                if ($submenuItem->getRole()) {
                    echo("\n" . $submenuItem->getRole());
                    $this->checkAndCreateCredential(
                        $submenuItem->getRole(),
                        $rubrique,
                        method_exists($class, 'getLibelle') ? $submenuItem->getLibelle() : $submenuItem->getId(),
                        type: 'credential.submenu',
                    );
                }
            }
        }
    }
}
