<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Génère une copie WebP allégée (photo.jpg → photo.jpg.webp) à côté de chaque image JPEG/PNG.
 *
 * Le .htaccess sert ensuite automatiquement la version WebP aux navigateurs qui l'acceptent,
 * sans changer les URLs. L'original n'est jamais modifié (il reste servi aux autres navigateurs).
 */
class WebpGenerator
{
    /** Dossiers traités (relatifs à public/) et taille maximale (plus grand côté, en px). */
    public const DIRECTORIES = [
        'uploads/images/slides' => 1920,
        'uploads/images/application/icone' => 256,
        'uploads/images/logos' => 600,
        'uploads/images' => 1200,
        'img' => 1600,
    ];

    private const QUALITY = 80;
    /** Au-delà (~100 Mo de RAM pour GD), l'image est ignorée : la réduire avant envoi, ou la convertir hors ligne. */
    private const MAX_PIXELS = 25_000_000;

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isSupported(): bool
    {
        return \function_exists('imagewebp') || class_exists(\Imagick::class);
    }

    /**
     * Parcourt les dossiers d'images et crée les WebP manquants (ou tous si $force).
     *
     * @return array{created: int, skipped: int, failed: list<string>}
     */
    public function generateMissing(bool $force = false, ?callable $progress = null): array
    {
        $result = ['created' => 0, 'skipped' => 0, 'failed' => []];
        $seen = [];

        foreach (self::DIRECTORIES as $dir => $maxSide) {
            $absDir = $this->publicDir . '/' . $dir;
            if (!is_dir($absDir)) {
                continue;
            }
            // Dossier parent (uploads/images) : on ne redescend pas dans les sous-dossiers déjà traités
            $files = glob($absDir . '/{,*/}*.{jpg,jpeg,png,JPG,JPEG,PNG}', \GLOB_BRACE) ?: [];
            foreach ($files as $file) {
                if (isset($seen[$file])) {
                    continue;
                }
                $seen[$file] = true;
                $status = $this->generate($file, $this->maxSideFor($file), $force);
                match ($status) {
                    'created' => $result['created']++,
                    'failed' => $result['failed'][] = substr($file, \strlen($this->publicDir) + 1),
                    default => $result['skipped']++,
                };
                if ($progress) {
                    $progress($file, $status);
                }
            }
        }

        return $result;
    }

    /** @return 'created'|'skipped'|'failed' */
    public function generate(string $source, int $maxSide, bool $force = false): string
    {
        $target = $source . '.webp';
        if (!$force && is_file($target) && filemtime($target) >= filemtime($source)) {
            return 'skipped';
        }
        $info = @getimagesize($source);
        if (!$info || !\in_array($info[2], [\IMAGETYPE_JPEG, \IMAGETYPE_PNG], true)) {
            return 'failed';
        }
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            $this->logger->warning('WebP ignoré, image trop grande : {file}', ['file' => $source]);
            return 'failed';
        }

        try {
            $tmp = $target . '.tmp';
            $ok = \function_exists('imagewebp')
                ? $this->withGd($source, $tmp, $info, $maxSide)
                : $this->withImagick($source, $tmp, $maxSide);
            // On ne garde le WebP que s'il est réellement plus léger que l'original
            if ($ok && filesize($tmp) < filesize($source)) {
                rename($tmp, $target);
                return 'created';
            }
            @unlink($tmp);
            return 'skipped';
        } catch (\Throwable $e) {
            $this->logger->error('Échec conversion WebP {file} : {error}', ['file' => $source, 'error' => $e->getMessage()]);
            return 'failed';
        }
    }

    private function maxSideFor(string $file): int
    {
        $relative = substr(\dirname($file), \strlen($this->publicDir) + 1);
        foreach (self::DIRECTORIES as $dir => $maxSide) {
            if ($relative === $dir || str_starts_with($relative, $dir . '/')) {
                return $maxSide;
            }
        }
        return 1200;
    }

    private function withGd(string $source, string $target, array $info, int $maxSide): bool
    {
        $image = $info[2] === \IMAGETYPE_PNG ? imagecreatefrompng($source) : imagecreatefromjpeg($source);
        if (!$image) {
            return false;
        }
        if ($info[2] === \IMAGETYPE_JPEG && \function_exists('exif_read_data')) {
            $orientation = @exif_read_data($source)['Orientation'] ?? 1;
            $image = match ((int) $orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = min(1, $maxSide / max($width, $height));
        if ($ratio < 1) {
            $resized = imagescale($image, (int) round($width * $ratio), (int) round($height * $ratio), \IMG_BICUBIC);
            imagedestroy($image);
            $image = $resized;
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $ok = imagewebp($image, $target, self::QUALITY);
        imagedestroy($image);

        return $ok;
    }

    private function withImagick(string $source, string $target, int $maxSide): bool
    {
        $image = new \Imagick($source);
        $image->autoOrient();
        $image->stripImage();
        if (max($image->getImageWidth(), $image->getImageHeight()) > $maxSide) {
            $image->thumbnailImage($maxSide, $maxSide, true);
        }
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality(self::QUALITY);
        $ok = $image->writeImage($target);
        $image->clear();

        return $ok;
    }
}
