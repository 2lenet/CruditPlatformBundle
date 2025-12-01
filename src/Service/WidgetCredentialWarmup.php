<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Lle\CruditBundle\Registry\MenuRegistry;
use Lle\DashboardBundle\Service\WidgetProvider;

class WidgetCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        protected WidgetProvider $widgetProvider,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function warmUp(): void
    {
        $rubrique = "Widgets";
        $i = 0;
        foreach ($this->widgetProvider->getWidgetTypes() as $widget) {
            $this->checkAndCreateCredential(
                $widget->getRole(),
                $rubrique,
                $widget->getName(),
                $i++,
                type: 'credential.widget'
            );
        }
    }
}
