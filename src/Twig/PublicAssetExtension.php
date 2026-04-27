<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PublicAssetExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('public_asset_exists', [$this, 'publicAssetExists']),
        ];
    }

    public function publicAssetExists(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        $normalizedPath = str_replace('\\', '/', ltrim($path, '/'));
        if (str_contains($normalizedPath, '..') || preg_match('#^[a-z]+://#i', $normalizedPath) === 1) {
            return false;
        }

        $publicDir = realpath($this->projectDir . '/public');
        if ($publicDir === false) {
            return false;
        }

        $assetPath = realpath($publicDir . '/' . $normalizedPath);

        return $assetPath !== false
            && str_starts_with($assetPath, $publicDir)
            && is_file($assetPath);
    }
}
