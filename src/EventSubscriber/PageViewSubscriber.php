<?php

namespace App\EventSubscriber;

use App\Entity\PageView;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Enregistre une vue de page pour les statistiques du dashboard.
 *
 * Écoute kernel.terminate (après l'envoi de la réponse) : l'écriture en base
 * ne ralentit pas la page et une base indisponible ne fait pas tomber le site.
 * Seules les réponses 200 des visiteurs anonymes sont comptées (pas les 404,
 * pas les scans de robots, pas les administrateurs connectés).
 */
class PageViewSubscriber implements EventSubscriberInterface
{
    // User-agents de bots connus à ignorer
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
        'linkedinbot', 'twitterbot', 'whatsapp', 'googlebot', 'bingbot',
        'yandex', 'baidu', 'semrush', 'ahrefs', 'mj12bot', 'dotbot',
        'curl', 'wget', 'python-requests', 'headless',
    ];

    // Extensions de fichiers statiques à ignorer
    private const IGNORED_EXTENSIONS = [
        'js', 'css', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'map', 'webp', 'xml', 'txt',
    ];

    private const IGNORED_PREFIXES = [
        '/admin', '/_', '/login', '/logout', '/register', '/verify', '/reset-password', '/search',
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::TERMINATE => ['onKernelTerminate', 0]];
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // Uniquement les pages réellement servies (pas les 404, redirections, erreurs)
        if ($response->getStatusCode() !== 200 || $request->getMethod() !== 'GET') {
            return;
        }

        $pathInfo = $request->getPathInfo();
        foreach (self::IGNORED_PREFIXES as $prefix) {
            if (str_starts_with($pathInfo, $prefix)) {
                return;
            }
        }

        $ext = strtolower(pathinfo($pathInfo, PATHINFO_EXTENSION));
        if (in_array($ext, self::IGNORED_EXTENSIONS, true)) {
            return;
        }

        $ua = strtolower($request->headers->get('User-Agent', ''));
        if ($ua === '') {
            return;
        }
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return;
            }
        }

        // Les administrateurs connectés ne sont pas des visiteurs
        if ($this->security->getUser() !== null) {
            return;
        }

        try {
            $pageView = new PageView();
            $pageView->setUrl(mb_substr($pathInfo, 0, 191));
            $pageView->setUserAgent(mb_substr($request->headers->get('User-Agent', ''), 0, 191));
            $pageView->setReferer(mb_substr($request->headers->get('Referer', ''), 0, 191) ?: null);

            // IP hashée (SHA-256 + salt = pseudonymisation RGPD)
            $ip = $request->getClientIp();
            if ($ip) {
                $pageView->setIpHash(hash('sha256', $ip . 'adivec_salt'));
            }

            $this->em->persist($pageView);
            $this->em->flush();
        } catch (\Throwable $e) {
            // Les statistiques ne doivent jamais faire échouer une requête
            $this->logger->warning('PageView non enregistrée : ' . $e->getMessage());
        }
    }
}
