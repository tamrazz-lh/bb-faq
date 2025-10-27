<?php

/*
Plugin Name: [ЛХ/БХ] FAQ
Description:  [bb-faq] - Добавляет Гутенберг Блок Вопросы-Ответы. Формирует разметку JSON-LD со структурированными данными FAQPage.
Author: ООО "Буферная бухта"
Author URI:  https://lifehacker.ru/
Requires PHP: 8.1
Version: 1.0.0
*/

use Bb\BbFaq\Gutenberg;

if (!defined('ABSPATH')) {
    exit;
}

define('BB_FAQ__PLUGIN_FILE', __FILE__);
define('BB_FAQ__PLUGIN_PATH', plugin_dir_path(BB_FAQ__PLUGIN_FILE));
define('BB_FAQ__PLUGIN_URL', plugin_dir_url(BB_FAQ__PLUGIN_FILE));

require trailingslashit(BB_FAQ__PLUGIN_PATH) . '/vendor/autoload.php';

function bb_faq__init()
{
    Gutenberg::init();
}

add_action('plugins_loaded', 'bb_faq__init', 10);
