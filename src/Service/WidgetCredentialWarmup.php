<?php

namespace Lle\CruditPlatformBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;
use Lle\DashboardBundle\Service\WidgetProvider;

class WidgetCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public function __construct(
        protected WidgetProvider $widgetProvider,
        protected CredentialRepository $credentialRepository,
        protected EntityManagerInterface $entityManager,
        protected CredentialFactory $credentialFactory,
    ) {
    }

    public function warmUp(): void
    {
        $rubrique = "Widgets";
        if ($this->widgetProvider->getWidgetTypes()) {
            foreach ($this->widgetProvider->getWidgetTypes() as $widget) {
                $this->checkAndCreateCredential(
                    $widget->getRole(),
                    $rubrique,
                    $widget->getName(),
                    type: 'credential.widget'
                );
            }
        }
    }
}
