<?php

namespace App\Twig;

use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SafeAssetExtension extends AbstractExtension
{
    private string $publicDir;

    public function __construct(
        private readonly Packages $packages,
        #[Autowire('%kernel.project_dir%')]
        string $projectDir
    ) {
        $this->publicDir = rtrim($projectDir, '\\/') . DIRECTORY_SEPARATOR . 'public';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('safe_asset', [$this, 'safeAsset']),
        ];
    }

    public function safeAsset(?string $path, ?string $fallback = null): ?string
    {
        if ($path === null) {
            return $fallback;
        }

        $path = trim($path);
        if ($path === '' || str_contains($path, "\0") || $this->hasSchemeOrNetworkPrefix($path)) {
            return $fallback;
        }

        $relativePath = ltrim(str_replace('\\', '/', $path), '/');
        if ($relativePath === '' || preg_match('#(^|/)\.\.(/|$)#', $relativePath)) {
            return $fallback;
        }

        $realPublicDir = realpath($this->publicDir);
        if ($realPublicDir === false) {
            return $fallback;
        }

        $candidatePath = $realPublicDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $realCandidate = realpath($candidatePath);
        if ($realCandidate === false || !is_file($realCandidate)) {
            return $fallback;
        }

        if (!$this->isInsideDirectory($realCandidate, $realPublicDir)) {
            return $fallback;
        }

        return $this->packages->getUrl($relativePath);
    }

    private function hasSchemeOrNetworkPrefix(string $path): bool
    {
        return (bool) preg_match('#^(?:[a-z][a-z0-9+.-]*:|//|\\\\\\\\)#i', $path);
    }

    private function isInsideDirectory(string $path, string $directory): bool
    {
        $normalizedPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $normalizedDirectory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (DIRECTORY_SEPARATOR === '\\') {
            $normalizedPath = strtolower($normalizedPath);
            $normalizedDirectory = strtolower($normalizedDirectory);
        }

        return str_starts_with($normalizedPath, $normalizedDirectory);
    }
}
