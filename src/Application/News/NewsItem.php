<?php

declare(strict_types=1);

namespace Darkheim\Application\News;

final readonly class NewsItem
{
    public function __construct(
        public int    $id,
        public string $title,
        public string $author,
        public int    $date,
        public array  $translations = [],
    ) {}

    public function titleForLanguage(string $language): string
    {
        if ($language !== '' && isset($this->translations[$language])) {
            $decoded = base64_decode($this->translations[$language], true);
            if ($decoded !== false && $decoded !== '') {
                return $this->fixEncoding($decoded);
            }
        }

        return $this->fixEncoding($this->title);
    }

    private function fixEncoding(string $text): string
    {
        // If already valid UTF-8, return as-is
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        // Try multiple encodings in order of likelihood
        $encodings = ['Windows-1252', 'ISO-8859-1', 'CP1252', 'UTF-8'];
        
        foreach ($encodings as $encoding) {
            $converted = @iconv($encoding, 'UTF-8//IGNORE', $text);
            if ($converted !== false && $converted !== '' && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        // Fallback: force UTF-8 conversion, replacing invalid chars
        return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }

    public function url(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/news/' . $this->id . '/';
    }
}

