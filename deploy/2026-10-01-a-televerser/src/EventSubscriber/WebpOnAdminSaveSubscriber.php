<?php

namespace App\EventSubscriber;

use App\Service\WebpGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Après chaque enregistrement dans l'admin (produit, news, slide…), crée la version WebP
 * des images nouvellement envoyées. Seules les images sans WebP sont traitées : c'est rapide.
 */
class WebpOnAdminSaveSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly WebpGenerator $generator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onSave',
            AfterEntityUpdatedEvent::class => 'onSave',
        ];
    }

    public function onSave(): void
    {
        if ($this->generator->isSupported()) {
            $this->generator->generateMissing();
        }
    }
}
