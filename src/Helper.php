<?php

namespace Bb\BbFaq;

class Helper
{
    private const PRJ_LH = 'lifehacker';
    private const PRJ_BH = 'burninghut';

    /**
     * Определяет название проекта по URL.
     *
     * @return string
     * @throws \RuntimeException
     */
    public static function getProject(): string
    {
        $url = mb_strtolower(home_url());
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host && str_contains($url, '/')) {
            $host = parse_url('https://' . $url, PHP_URL_HOST);
        }
        if (empty($host)) {
            throw new \RuntimeException('bb-faq: Ошибка получения host', 1);
        }
    
        $parts = explode('.', $host);
        if (count($parts) < 2) {
            throw new \RuntimeException('bb-faq: Ошибка парсинга домена', 1);
        }

        $domain = $parts[count($parts) - 2];
        return match ($domain) {
            'lifehacker' => self::PRJ_LH,
            'burninghut' => self::PRJ_BH,
            default => throw new \RuntimeException("bb-faq: Проект с доменом {$domain} не зарегистрирован", 1),
        };
    }

    /**
     * Определяет название проекта по URL.
     *
     * @return bool
     */
    public static function isLhProject(): bool
    {
        try {
            $prj = static::getProject();
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return false;
        }
        return $prj === self::PRJ_LH;
    }

}
