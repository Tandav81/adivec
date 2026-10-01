<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * {{ img_dims('uploads/images/products/' ~ product.image) }}  →  width="1200" height="800"
 *
 * Donne au navigateur les proportions de l'image avant son chargement (évite les décalages
 * de mise en page, métrique CLS). Le CSS :where(img[width][height]) { height: auto } garde
 * le rendu responsive. Image absente ou SVG : rien n'est ajouté.
 */
class ImageExtension extends AbstractExtension
{
    /** @var array<string, string> */
    private array $memo = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('img_dims', [$this, 'dimensions'], ['is_safe' => ['html']]),
        ];
    }

    public function dimensions(?string $path): string
    {
        $path = ltrim((string) $path, '/');
        if ($path === '' || str_ends_with($path, '/')) {
            return '';
        }

        return $this->memo[$path] ??= (function () use ($path): string {
            $size = @getimagesize($this->publicDir . '/' . $path);

            return $size ? sprintf('width="%d" height="%d"', $size[0], $size[1]) : '';
        })();
    }
}
