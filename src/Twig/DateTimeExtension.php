<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatDate', [$this, 'formatDate']),
            new TwigFilter('formatDateTime', [$this, 'formatDateTime']),
        ];
    }

    public function formatDate(\DateTimeInterface $date = null): string
    {
        if (!$date) {
            return '';
        }
        return $date->format('d/m/Y');
    }

    public function formatDateTime(\DateTimeInterface $date = null): string
    {
        if (!$date) {
            return '';
        }
        return $date->format('d/m/Y H:i:s');
    }
}
