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

    /**
     * Возвращает все публичные типы постов
     * в текущем проекте.
     * 
     * @return array
     */
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

    /**
     * Возвращает Гутенберг блоки из поста.
     * С помощью аргументов можно:
     * - фильтровать блоки по имени
     * - ограничивать кол-во блоков в выдаче
     * - дополнять аттрибуты блока дефолтными значениями.
     * 
     * @var WP_Post $post
     * @var string $blockName
     * @var int $limit
     * @var bool $withDefaults
     * 
     * @return array
     */
    public static function getPostBlocks(\WP_Post $post, string $blockName = '', int $limit = 0, bool $withDefaults = true): array
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
            }
            $blocks = $filteredBlocks;
        }
        if ($withDefaults) {
            foreach ($blocks as &$block) {
                if (empty($block['blockName'])) {
                    continue;
                }
                if (empty($block['attrs'])) {
                    $block['attrs'] = [];
                }
                $defaultAttrs = array_map(function ($attributes) {
                    return $attributes['default'] ?? '';
                }, static::getBlockAttributesFromJson($block['blockName']));
                $block['attrs'] = array_merge($defaultAttrs, $block['attrs']);
            }
        }
        if ($limit > 0) {
            $blocks = array_slice($blocks, 0, $limit);
        }
        return $blocks;
    }

    /**
     * Возвращает массив аттрибутов блока,
     * взятых из JSON-описания блока.
     * Работает только с блоками, описанными в этом плагине. 
     * 
     * @var string $blockName
     * 
     * @return array
     */
    public static function getBlockAttributesFromJson(string $blockName): array
    {
        $blockJsonDir = ucfirst(str_replace(['lh/', 'bh/', 'bb/'], ['', '', ''], $blockName));
        $blockJsonPath = BB_FAQ__PLUGIN_PATH . "blocks/{$blockJsonDir}/block.json";
        
        if (!file_exists($blockJsonPath)) {
            return [];
        }

        $blockJson = file_get_contents($blockJsonPath);
        $blockData = json_decode($blockJson, true);

        return $blockData['attributes'] ?? [];
    }

    /**
     * Очистка строки от html-тегов.
     * 
     * @var string $rawText
     * 
     * @return string
     */
    public static function sanitizeTextField(string $rawText): string
    {
        $text = wp_check_invalid_utf8($rawText);
        if ($text === '') {
            return '';
        }

        $text = wp_strip_all_tags($text, true);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);

        return $text;
    }

    /**
     * Очистка массива строки от html-тегов.
     * Обрабатывает массив рекурсивно, вместе с вложенными массивами.
     * 
     * @var array $textFields
     * 
     * @return array
     */
    public static function sanitizeTextFieldArray(array $textFields): array
    {
        $sanitized = [];

        foreach ($textFields as $key => $field) {
            if (is_string($field)) {
                $sanitized[$key] = static::sanitizeTextField($field);
            } elseif (is_array($field)) {
                $sanitized[$key] = static::sanitizeTextFieldArray($field);
            } else {
                $sanitized[$key] = $field;
            }
        }

        return $sanitized;
    }
}
