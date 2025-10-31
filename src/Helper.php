<?php

namespace Bb\BbFaq;

class Helper
{
    private const PRJ_LH = 'lifehacker';
    private const PRJ_BH = 'burninghut';

    /**
     * Проверка текущего пользователя,
     * на права редактировать пост.
     * 
     * @return bool
     */
    public static function canEditPost(): bool
    {
        return current_user_can('edit_posts');
    }
    
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

    public static function getPostTypes(): array
    {
        $types = array_values(get_post_types([
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
        ]));
    
        $exclude = [
            'revision',
            'attachment',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
        ];
    
        return array_diff($types, $exclude);
    }

    public static function getPostBlocks(\WP_Post $post, string $blockName = '', int $limit = 0): array
    {
        $blocks = parse_blocks($post->post_content);
        if (empty($blocks)) {
            return [];
        }
        if (!empty($blockName)) {
            $filteredBlocks = [];
            foreach ($blocks as $block) {
                if (isset($block['blockName']) && $block['blockName'] === $blockName) {
                    $filteredBlocks[] = $block;
                }
                if ($limit && $limit === count($filteredBlocks)) {
                    break;
                }
            }
            $blocks = $filteredBlocks;
        }
        if ($limit > 0) {
            $blocks = array_slice($blocks, 0, $limit);
        }
        return $blocks;
    }

    // public static function findFirstFaqBlock(array $blocks): ?array
    // {
    //     foreach ($blocks as $block) {
    //         if (isset($block['blockName']) && $block['blockName'] === 'bb/faq') {
    //             return $block;
    //         }

    //         if (!empty($block['innerBlocks'])) {
    //             $found = self::findFirstFaqBlock($block['innerBlocks']);
    //             if ($found) {
    //                 return $found;
    //             }
    //         }
    //     }

    //     return null;
    // }
}
