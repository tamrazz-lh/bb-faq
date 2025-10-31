<?php

namespace Bb\BbFaq;

class Gutenberg
{
    public const BUILD_DIR = BB_FAQ__PLUGIN_PATH . 'build/blocks/';
    public const BUILD_URL = BB_FAQ__PLUGIN_URL . 'build/blocks/';
    public const JS_HANDLER = 'bb-faq';
    public const META_KEY = '_bb_faq_block_data';

    /**
     * Статичная инициализация класса.
     *
     * @return void
     */
    public static function init(): void
    {
        static::initAssets();
        static::initBlocks();
        static::registerMeta();
        static::registerHooks();
    }

    /**
     * Инициализация скриптов и стилей.
     *
     * @return void
     */
    private static function initAssets(): void
    {
        $assets = static::BUILD_DIR . 'index.asset.php';
        if (!file_exists($assets)) {
            return;
        }

        $settings = include $assets;

        add_action('admin_enqueue_scripts', function () use ($settings) {
            $screen = get_current_screen();
            if ($screen->base !== 'post') {
                return;
            }

            if (file_exists(static::BUILD_DIR . 'index.js')) {
                wp_enqueue_script(
                    static::JS_HANDLER,
                    static::BUILD_URL . 'index.js',
                    $settings['dependencies'],
                    $settings['version']
                );
            }

            if (file_exists(static::BUILD_DIR . 'index.css')) {
                wp_enqueue_style(
                    static::JS_HANDLER,
                    static::BUILD_URL . 'index.css',
                    [],
                    $settings['version']
                );
            }
        });
    }

    /**
     * Инициализация блоков Гутенберг в админке.
     * Необходимо так как на ЛХ появляются блоки
     * только из специального белого листа.
     * Этим хуком мы добавляем блоки в этот белый лист.
     *
     * @return void
     */
    private static function initBlocks(): void
    {
        if (!Helper::isLhProject()) {
            return;
        }
        $class = '\Lifehacker\GutenbergBlocks\App';
        $get = 'getBlocks';
        $add = 'addBlocks';
        if (!class_exists($class) || !method_exists($class, $get) || !method_exists($class, $add)) {
            return;
        }
        $blocks = $class::$get(BB_FAQ__PLUGIN_FILE, 'blocks');
        $class::$add($blocks);
    }

    /**
     * Регистрация хуков.
     *
     * @return void
     */
    private static function registerHooks(): void
    {
        add_action('wp_after_insert_post', [__CLASS__, 'saveFaqMeta'], 10, 2);
    }

    /**
     * Регистрация мета-полей.
     *
     * @return void
     */
    public static function registerMeta(): void
    {
        foreach (Helper::getPostTypes() as $postType) {
            register_post_meta($postType, self::META_KEY, [
                'show_in_rest' => true,
                'single' => true,
                'default' => '',
                'type' => 'string',
                'auth_callback' => [Helper::class, 'canEditPost'],
            ]);
        }
    }

    /**
     * Хук.
     * Сохраняет в мета-данных поста иформацию из блока FAQ.
     * Нужно для построения схемы JSON-LD.
     * 
     * @var int $postId
     * @var WP_Post $post
     * 
     * @return void
     */
    public static function saveFaqMeta(int $postId, \WP_Post $post): void
    {
        if (wp_is_post_revision($postId) || wp_is_post_autosave($postId)) {
            return;
        }

        if (!in_array($post->post_type, Helper::getPostTypes())) {
            return;
        }

        if (empty($post->post_content)) {
            delete_post_meta($postId, self::META_KEY);
            return;
        }

        $faqData = static::getFaqAttributesFromPost($post);
        update_post_meta($postId, self::META_KEY, json_encode($faqData, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Вспомогательная функция.
     * Извлекает из контента поста аттрибуты блока FAQ.
     * 
     * @var WP_Post $post
     * @var bool $sanitize
     * 
     * @return void
     */
    private static function getFaqAttributesFromPost(\WP_Post $post, bool $sanitize = true): array
    {
        if (empty($post->post_content)) {
            return [];
        }

        $blocks = Helper::getPostBlocks($post, 'bb/faq', 1, true);
        if (empty($blocks[0]['attrs'])) {
            return [];
        }

        $attrs = $blocks[0]['attrs'];

        if ($sanitize) {
            $attrs = Helper::sanitizeTextFieldArray($attrs);
        }

        return $attrs;
    }
}
