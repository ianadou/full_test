<?php

declare(strict_types=1);

namespace App\Service;

final class StringHelper
{
    public function capitalize(?string $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }

        return ucfirst(strtolower($str));
    }

        public function slugify(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        return trim($slug, '-');
    }
}
