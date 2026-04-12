<?php

namespace App\EventSubscriber;

use App\Entity\PageView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PageViewSubscriber implements EventSubscriberInterface
{
    // User-agents de bots connus à ignorer
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
        'linkedinbot', 'twitterbot', 'whatsapp', 'googlebot', 'bingbot',
        'yandex', 'baidu', 'semrush', 'ahrefs', 'mj12bot', 'dotbot',
    ];

    // Extensions de fichiers statiques à ignorer
    private const IGNORED_EXTENSIONS = [
        'js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'map', 'webp', 'xml', 'txt',
    ];

    public function __construct(private readonly EntityManagerInterface $em) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 0]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Ignorer les routes admin et internes
        $pathInfo = $request->getPathInfo();
        if (str_starts_with($pathInfo, '/admin') ||
            str_starts_with($pathInfo, '/_') ||
            str_starts_with($pathInfo, '/login') ||
            str_starts_with($pathInfo, '/register') ||
            str_starts_with($pathInfo, '/reset-password') ||
            str_starts_with($pathInfo, '/search')) {
            return;
        }

        // Ignorer les fichiers statiques
        $ext = strtolower(pathinfo($pathInfo, PATHINFO_EXTENSION));
        if (in_array($ext, self::IGNORED_EXTENSIONS, true)) {
            return;
        }

        // Ignorer les bots
        $ua = strtolower($request->headers->get('User-Agent', ''));
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return;
            }
        }

        // Ignorer les requêtes non-GET (formulaires, etc.)
        if ($request->getMethod() !== 'GET') {
            return;
        }

        // Enregistrer la visite
        $pageView = new PageView();
        $pageView->setUrl($pathInfo);
        $pageView->setUserAgent(mb_substr($request->headers->get('User-Agent', ''), 0, 500));
        $pageView->setReferer(mb_substr($request->headers->get('Referer', ''), 0, 500) ?: null);

        // IP hashée (SHA-256 + salt = pseudonymisation RGPD)
        $ip = $request->getClientIp();
        if ($ip) {
            $pageView->setIpHash(hash('sha256', $ip . 'adivec_salt'));
        }

        $this->em->persist($pageView);
        $this->em->flush();
    }
}
