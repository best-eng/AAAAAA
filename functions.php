<?php
// Тема: Tema-souz – функции темы

// Безопасность: запрет прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

// Подключаем динамические изображения по типу проката
require_once get_stylesheet_directory() . '/dynamic-images-by-rental-type.php';


// Поддержка темы и WooCommerce
add_action('after_setup_theme', function () {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('woocommerce');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'comment-form', 'comment-list', 'style', 'script']);
});

// Включение тестового шаблона single при параметре ?test=1
add_filter('template_include', function($template){
    if (function_exists('is_product') && is_product() && isset($_GET['test']) && $_GET['test'] == '1') {
        $custom = get_stylesheet_directory() . '/woocommerce/single-product-new.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 99);

// Регистрация меню
add_action('init', function () {
	register_nav_menus([
        'primary' => __('Главное меню', 'tema-souz'),
        'footer'  => __('Меню в подвале', 'tema-souz'),
	]);
});

// Подключение ассетов из /assets 1:1 с HTML
add_action('wp_enqueue_scripts', function () {
	$theme_uri = get_stylesheet_directory_uri();
    $version = '1.0.10'; // Увеличивайте при изменениях

    // CSS - базовые стили для всех страниц
    wp_enqueue_style('tema-souz-styles', $theme_uri . '/assets/css/styles.css', [], $version);
    wp_enqueue_style('tema-souz-header', $theme_uri . '/assets/css/header.css', ['tema-souz-styles'], $version);
    wp_enqueue_style('tema-souz-footer', $theme_uri . '/assets/css/footer.css', ['tema-souz-styles'], $version);
    
    // CSS для Contact Form 7 - вынесен в отдельный файл
    if (function_exists('wpcf7')) {
        wp_enqueue_style('tema-souz-cf7', $theme_uri . '/assets/css/cf7-custom.css', ['tema-souz-styles'], $version);
    }
    
    // CSS для страницы товара - условная загрузка
    if (is_product() || is_singular('product') || get_post_type() === 'product' || 
        (isset($_GET['product']) && !empty($_GET['product'])) ||
        (is_single() && get_post_type() === 'product')) {
        
        wp_enqueue_style('tema-souz-product-card', $theme_uri . '/assets/css/product-card.css', ['tema-souz-styles'], $version);
        wp_enqueue_style('tema-souz-services', $theme_uri . '/assets/css/services.css', ['tema-souz-styles'], $version);
    }

    // JS - основной скрипт с defer
    wp_enqueue_script('tema-souz-script', $theme_uri . '/assets/js/script.js', [], $version, true);
    wp_script_add_data('tema-souz-script', 'defer', true);
    wp_localize_script('tema-souz-script', 'TemaSouz', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
    
    // Swiper.js - ТОЛЬКО для страниц со слайдерами
    $needs_swiper = is_front_page() || is_page_template('page-about.php') || 
                    is_post_type_archive('news') || is_singular('news') || is_tax('news_tag') ||
                    is_post_type_archive('service');
    
    if ($needs_swiper) {
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true);
        wp_script_add_data('swiper', 'defer', true);
        wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0');
    }

    // Яндекс.Карты — подключаем на всех статических страницах и главной
    $needs_yandex_maps = is_front_page() || is_page() || is_singular('service');
    
    if ($needs_yandex_maps) {
        $yandex_api_key = get_option('tema_souz_yamaps_api_key');
        $yamaps_src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU' . ($yandex_api_key ? '&apikey=' . urlencode($yandex_api_key) : '');
        wp_enqueue_script('yandex-maps', $yamaps_src, [], null, true);
        wp_script_add_data('yandex-maps', 'defer', true);
    }
    
    // Page specific assets
    if (is_page_template('page-about.php')) {
        wp_enqueue_style('tema-souz-about', $theme_uri . '/assets/css/about.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-about', $theme_uri . '/assets/js/about.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-about', 'defer', true);
    }
    if (is_page_template('page-b2b.php')) {
        wp_enqueue_style('tema-souz-b2b', $theme_uri . '/assets/css/b2b.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-b2b', $theme_uri . '/assets/js/b2b.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-b2b', 'defer', true);
    }
    if (is_page_template('page-contacts.php')) {
        wp_enqueue_style('tema-souz-contacts', $theme_uri . '/assets/css/contacts.css', ['tema-souz-styles'], $version);
        // contacts.js не подключаем — содержит устаревшую initMap() которая конфликтует
    }
    // WooCommerce assets
    if (function_exists('is_shop') && (is_shop() || is_page_template('page-catalog.php'))) {
        wp_enqueue_style('tema-souz-catalog', $theme_uri . '/assets/css/catalog.css', ['tema-souz-styles'], $version);
        wp_enqueue_style('tema-souz-products', $theme_uri . '/assets/css/products.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-products', $theme_uri . '/assets/js/products.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-products', 'defer', true);
    }
    
    // Отключаем стандартные стили Contact Form 7
    add_action('wp_print_styles', 'tema_souz_dequeue_cf7_styles');
    function tema_souz_dequeue_cf7_styles() {
        wp_dequeue_style('contact-form-7');
        wp_deregister_style('contact-form-7');
    }
    
    // JavaScript для страницы товара - условная загрузка (НЕ на странице архива)
    if ((is_product() || is_singular('product') || get_post_type() === 'product' || 
        (isset($_GET['product']) && !empty($_GET['product'])) ||
        (is_single() && get_post_type() === 'product')) && 
        !is_product_category()) {
        
        wp_enqueue_script('tema-souz-product-card', $theme_uri . '/assets/js/product-card.js', ['tema-souz-script'], $version, true);
        wp_enqueue_script('tema-souz-services', $theme_uri . '/assets/js/services.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-product-card', 'defer', true);
        wp_script_add_data('tema-souz-services', 'defer', true);
        // Добавляем archive-product.js для блока похожих товаров
        wp_enqueue_script('tema-souz-archive-product', $theme_uri . '/assets/js/archive-product.js', ['jquery'], $version, true);
        wp_script_add_data('tema-souz-archive-product', 'defer', true);
    }
    if (function_exists('is_product_category') && is_product_category()) {
        wp_enqueue_style('tema-souz-products', $theme_uri . '/assets/css/products.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-products', $theme_uri . '/assets/js/products.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-products', 'defer', true);
        // Добавляем JS для функционала количества и единиц измерения
        wp_enqueue_script('tema-souz-archive-product', $theme_uri . '/assets/js/archive-product.js', ['jquery'], $version, true);
        wp_script_add_data('tema-souz-archive-product', 'defer', true);
    }
    if (function_exists('is_cart') && is_cart()) {
        wp_enqueue_style('tema-souz-cart', $theme_uri . '/assets/css/cart.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-cart', $theme_uri . '/assets/js/cart.js', ['jquery'], $version, true);
        wp_script_add_data('tema-souz-cart', 'defer', true);
    }
    if (function_exists('is_checkout') && is_checkout()) {
        wp_enqueue_style('tema-souz-checkout', $theme_uri . '/assets/css/checkout.css', ['tema-souz-styles'], $version);
    }
    if (function_exists('is_order_received_page') && is_order_received_page()) {
        wp_enqueue_style('tema-souz-thankyou', $theme_uri . '/assets/css/thankyou.css', ['tema-souz-styles'], $version);
    }
    
    // Принудительная загрузка стилей товара для всех страниц с параметром product (кроме архива)
    if (isset($_GET['product']) && !empty($_GET['product']) && !is_product_category()) {
        wp_enqueue_style('tema-souz-product-card', $theme_uri . '/assets/css/product-card.css', ['tema-souz-styles'], $version);
        wp_enqueue_style('tema-souz-services', $theme_uri . '/assets/css/services.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-product-card', $theme_uri . '/assets/js/product-card.js', ['tema-souz-script'], $version, true);
        wp_enqueue_script('tema-souz-services', $theme_uri . '/assets/js/services.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-product-card', 'defer', true);
        wp_script_add_data('tema-souz-services', 'defer', true);
    }
});

// Отключение редиректа на страницу корзины после добавления товара
add_filter('woocommerce_add_to_cart_redirect', '__return_false');

// Удаление ссылки "Просмотр корзины" после добавления товара
add_filter('woocommerce_add_to_cart_message_html', '__return_false');

// Дополнительное отключение всех сообщений WooCommerce после добавления в корзину
add_filter('wc_add_to_cart_message_html', '__return_false');
add_filter('woocommerce_cart_redirect_after_error', '__return_false');

// Удаляем фрагменты корзины которые могут содержать ссылку "Просмотр корзины"
add_filter('woocommerce_add_to_cart_fragments', 'tema_souz_remove_cart_fragments', 10, 1);
function tema_souz_remove_cart_fragments($fragments) {
    // Удаляем фрагмент с сообщением о добавлении
    if (isset($fragments['div.woocommerce-message'])) {
        unset($fragments['div.woocommerce-message']);
    }
    return $fragments;
}

// Полное отключение всех уведомлений на странице корзины
add_action('woocommerce_before_cart', 'tema_souz_remove_cart_notices', 1);
function tema_souz_remove_cart_notices() {
    wc_clear_notices();
}

// Отключение уведомлений после обновления корзины
add_filter('woocommerce_cart_updated_message', '__return_false');
add_filter('woocommerce_cart_item_removed_message', '__return_false');
add_filter('woocommerce_cart_item_restored_message', '__return_false');

// Отключение купонов на странице оформления заказа
add_filter('woocommerce_coupons_enabled', 'tema_souz_disable_checkout_coupons');
function tema_souz_disable_checkout_coupons($enabled) {
    if (is_checkout()) {
        return false;
    }
    return $enabled;
}

// Отключение способов оплаты - заказ отправляется на почту
add_filter('woocommerce_cart_needs_payment', '__return_false');

// Автоматическое завершение заказа после оформления
add_action('woocommerce_thankyou', 'tema_souz_auto_complete_order');
function tema_souz_auto_complete_order($order_id) {
    if (!$order_id) {
        return;
    }
    
    $order = wc_get_order($order_id);
    
    if ($order && $order->get_status() === 'processing') {
        $order->update_status('completed');
    }
}

// Изменение текста кнопки оформления заказа
add_filter('woocommerce_order_button_text', 'tema_souz_custom_order_button_text');
function tema_souz_custom_order_button_text() {
    return 'Отправить заявку';
}

// Настройка полей checkout - только контактные данные
add_filter('woocommerce_checkout_fields', 'tema_souz_custom_checkout_fields');
function tema_souz_custom_checkout_fields($fields) {
    
    // Удаляем все ненужные поля billing
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);
    
    // Удаляем все поля shipping (адрес доставки)
    unset($fields['shipping']);
    
    // Настраиваем оставшиеся поля
    $fields['billing']['billing_first_name']['label'] = 'Имя';
    $fields['billing']['billing_first_name']['placeholder'] = 'Максим';
    $fields['billing']['billing_first_name']['required'] = true;
    $fields['billing']['billing_first_name']['class'] = array('form-row-wide');
    $fields['billing']['billing_first_name']['priority'] = 10;
    
    $fields['billing']['billing_phone']['label'] = 'Телефон';
    $fields['billing']['billing_phone']['placeholder'] = '+7 (999) 123-45-67';
    $fields['billing']['billing_phone']['required'] = true;
    $fields['billing']['billing_phone']['class'] = array('form-row-wide');
    $fields['billing']['billing_phone']['priority'] = 20;
    
    $fields['billing']['billing_email']['label'] = 'Email';
    $fields['billing']['billing_email']['placeholder'] = 'example@mail.com';
    $fields['billing']['billing_email']['required'] = true;
    $fields['billing']['billing_email']['class'] = array('form-row-wide');
    $fields['billing']['billing_email']['priority'] = 30;
    
    // Добавляем поле ИНН
    $fields['billing']['billing_inn'] = array(
        'type' => 'text',
        'label' => 'ИНН организации',
        'placeholder' => '1234567890',
        'required' => false,
        'class' => array('form-row-wide'),
        'priority' => 40
    );
    
    // Настраиваем комментарий к заказу
    $fields['order']['order_comments']['label'] = 'Комментарий к заказу';
    $fields['order']['order_comments']['placeholder'] = 'Укажите дополнительную информацию о заказе';
    $fields['order']['order_comments']['required'] = false;
    
    return $fields;
}

// Сохранение ИНН в мета-данные заказа
add_action('woocommerce_checkout_update_order_meta', 'tema_souz_save_inn_field');
function tema_souz_save_inn_field($order_id) {
    if (!empty($_POST['billing_inn'])) {
        update_post_meta($order_id, '_billing_inn', sanitize_text_field($_POST['billing_inn']));
    }
}

// Отображение ИНН в админке заказа
add_action('woocommerce_admin_order_data_after_billing_address', 'tema_souz_display_inn_in_admin');
function tema_souz_display_inn_in_admin($order) {
    $inn = get_post_meta($order->get_id(), '_billing_inn', true);
    if ($inn) {
        echo '<p><strong>ИНН организации:</strong> ' . esc_html($inn) . '</p>';
    }
}

// Добавление поля загрузки файла на странице checkout
add_action('woocommerce_after_checkout_billing_form', 'tema_souz_add_file_upload_field');
function tema_souz_add_file_upload_field($checkout) {
    echo '<div class="file-upload-section">';
    echo '<h3>Прикрепить файл</h3>';
    echo '<p class="file-upload-description">Вы можете прикрепить файл к заявке (DOC, PDF, XLS, XLSX). Максимальный размер: 10 МБ</p>';
    echo '<input type="file" name="order_attachment" id="order_attachment" accept=".doc,.docx,.pdf,.xls,.xlsx" />';
    echo '</div>';
}

// Обработка загрузки файла
add_action('woocommerce_checkout_update_order_meta', 'tema_souz_save_uploaded_file');
function tema_souz_save_uploaded_file($order_id) {
    if (!empty($_FILES['order_attachment']['name'])) {
        
        // Проверка типа файла
        $allowed_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $file_type = $_FILES['order_attachment']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            wc_add_notice('Неверный формат файла. Разрешены только DOC, PDF, XLS, XLSX', 'error');
            return;
        }
        
        // Проверка размера файла (10 МБ)
        if ($_FILES['order_attachment']['size'] > 10485760) {
            wc_add_notice('Файл слишком большой. Максимальный размер: 10 МБ', 'error');
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        $uploaded_file = $_FILES['order_attachment'];
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Сохраняем URL файла в мета-данные заказа
            update_post_meta($order_id, '_order_attachment', $movefile['url']);
            update_post_meta($order_id, '_order_attachment_name', $uploaded_file['name']);
        } else {
            wc_add_notice('Ошибка загрузки файла: ' . $movefile['error'], 'error');
        }
    }
}

// Отображение прикрепленного файла в админке заказа
add_action('woocommerce_admin_order_data_after_order_details', 'tema_souz_display_attachment_in_admin');
function tema_souz_display_attachment_in_admin($order) {
    $attachment_url = get_post_meta($order->get_id(), '_order_attachment', true);
    $attachment_name = get_post_meta($order->get_id(), '_order_attachment_name', true);
    
    if ($attachment_url) {
        echo '<div class="order-attachment" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
        echo '<h4>Прикрепленный файл:</h4>';
        echo '<p><a href="' . esc_url($attachment_url) . '" target="_blank" download>' . esc_html($attachment_name) . '</a></p>';
        echo '</div>';
    }
}

// Добавление ссылки на файл в email уведомления
add_action('woocommerce_email_after_order_table', 'tema_souz_add_attachment_to_email', 10, 4);
function tema_souz_add_attachment_to_email($order, $sent_to_admin, $plain_text, $email) {
    $attachment_url = get_post_meta($order->get_id(), '_order_attachment', true);
    $attachment_name = get_post_meta($order->get_id(), '_order_attachment_name', true);
    
    if ($attachment_url) {
        if ($plain_text) {
            echo "\n\nПрикрепленный файл: " . $attachment_name . "\n";
            echo "Ссылка для скачивания: " . $attachment_url . "\n";
        } else {
            echo '<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">';
            echo '<h3>Прикрепленный файл:</h3>';
            echo '<p><a href="' . esc_url($attachment_url) . '" target="_blank" style="color: #cb0000; text-decoration: none;">' . esc_html($attachment_name) . '</a></p>';
            echo '</div>';
        }
    }
}

// Отключаем необходимость адреса доставки
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');

// JavaScript для удаления дублирующихся элементов на странице checkout
add_action('wp_footer', 'tema_souz_remove_duplicate_checkout_elements');
function tema_souz_remove_duplicate_checkout_elements() {
    if (!is_checkout() || is_order_received_page()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Удаляем все блоки place-order кроме первого внутри #payment
        var paymentBlock = document.getElementById('payment');
        if (paymentBlock) {
            var placeOrderBlocks = paymentBlock.querySelectorAll('.place-order');
            if (placeOrderBlocks.length > 1) {
                for (var i = 1; i < placeOrderBlocks.length; i++) {
                    placeOrderBlocks[i].remove();
                }
            }
        }
        
        // Удаляем дублирующиеся кнопки #place_order
        var placeOrderButtons = document.querySelectorAll('#place_order');
        if (placeOrderButtons.length > 1) {
            for (var i = 1; i < placeOrderButtons.length; i++) {
                placeOrderButtons[i].remove();
            }
        }
    });
    </script>
    <?php
}

// JavaScript для скрытия дублирующихся сообщений CF7
add_action('wp_footer', 'tema_souz_cf7_hide_messages_js');
function tema_souz_cf7_hide_messages_js() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function hideCF7Messages() {
            const hiddenContainers = document.querySelectorAll('.hidden-fields-container');
            hiddenContainers.forEach(function(container) {
                container.style.display = 'none';
            });
            
            const allMessages = document.querySelectorAll('.wpcf7-response-output');
            if (allMessages.length > 1) {
                for (let i = 1; i < allMessages.length; i++) {
                    allMessages[i].style.display = 'none';
                }
            }
        }
        
        hideCF7Messages();
        
        document.addEventListener('wpcf7mailsent', function(event) {
            setTimeout(function() {
                hideCF7Messages();
                const successMessage = document.querySelector('.wpcf7-mail-sent-ok');
                if (successMessage) {
                    successMessage.style.display = 'block';
                }
            }, 100);
        });
        
        document.addEventListener('wpcf7invalid', function(event) {
            setTimeout(function() {
                hideCF7Messages();
                const errorMessage = document.querySelector('.wpcf7-validation-errors');
                if (errorMessage) {
                    errorMessage.style.display = 'block';
                }
            }, 100);
        });
        
        document.addEventListener('wpcf7spam', function(event) {
            setTimeout(hideCF7Messages, 100);
        });
        
        document.addEventListener('wpcf7mailfailed', function(event) {
            setTimeout(function() {
                hideCF7Messages();
                const failMessage = document.querySelector('.wpcf7-mail-sent-ng');
                if (failMessage) {
                    failMessage.style.display = 'block';
                }
            }, 100);
        });
    });
    </script>
    <?php
}

// AJAX обработчик для запроса расчета
add_action('wp_ajax_send_calculation_request', 'tema_souz_send_calculation_request');
add_action('wp_ajax_nopriv_send_calculation_request', 'tema_souz_send_calculation_request');

function tema_souz_send_calculation_request() {
    if (!wp_verify_nonce($_POST['nonce'], 'calculation_request_nonce')) {
        wp_die('Ошибка безопасности');
    }
    
    $name = sanitize_text_field($_POST['name']);
    $phone = sanitize_text_field($_POST['phone']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
    $product_title = sanitize_text_field($_POST['product_title']);
    $product_quantity = sanitize_text_field($_POST['product_quantity']);
    $product_unit = sanitize_text_field($_POST['product_unit']);
    $product_url = esc_url($_POST['product_url']);
    
    if (empty($name) || empty($phone)) {
        wp_send_json_error('Пожалуйста, заполните все обязательные поля');
    }
    
    $to = get_option('admin_email');
    $subject = 'Запрос расчета - ' . $product_title;
    
    $email_message = "
    <h2>Новый запрос расчета</h2>
    <p><strong>Имя:</strong> {$name}</p>
    <p><strong>Телефон:</strong> {$phone}</p>
    <p><strong>Email:</strong> " . ($email ? $email : 'Не указан') . "</p>
    <p><strong>Комментарий:</strong> " . ($message ? $message : 'Не указан') . "</p>
    <hr>
    <h3>Информация о товаре:</h3>
    <p><strong>Товар:</strong> {$product_title}</p>
    <p><strong>Количество:</strong> {$product_quantity} {$product_unit}</p>
    <p><strong>Ссылка на товар:</strong> <a href='{$product_url}'>{$product_url}</a></p>
    <hr>
    <p><em>Запрос отправлен " . current_time('d.m.Y H:i') . "</em></p>
    ";
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
    );
    
    $sent = wp_mail($to, $subject, $email_message, $headers);
    
    if ($sent) {
        wp_send_json_success('Запрос успешно отправлен');
    } else {
        wp_send_json_error('Ошибка при отправке письма');
    }
}

// Добавляем lazy loading для всех изображений
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    if (!is_admin()) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}, 10, 3);

// Добавляем lazy loading для post thumbnails
add_filter('post_thumbnail_html', function($html) {
    if (!is_admin() && !empty($html)) {
        $html = str_replace('<img', '<img loading="lazy"', $html);
    }
    return $html;
});


// Обработчик очистки корзины
add_action('init', function() {
    if (isset($_GET['empty-cart']) && $_GET['empty-cart'] === 'true' && function_exists('WC')) {
        WC()->cart->empty_cart();
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
});

// Если корзина пуста — не показывать новинки/товары на странице корзины (для WooCommerce Blocks)
// Вспомогательная: рекурсивно проверяет, содержит ли блок нужный блок по имени
if (!function_exists('tema_souz_block_contains')) {
    function tema_souz_block_contains($block, $targetName) {
        if (empty($block) || !is_array($block)) return false;
        $bn = isset($block['blockName']) ? (string) $block['blockName'] : '';
        if ($bn === $targetName) return true;
        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $ib) {
                if (tema_souz_block_contains($ib, $targetName)) return true;
            }
        }
        return false;
    }
}

add_filter('render_block', function ($block_content, $block) {
    // Целимся именно в страницу корзины (включая блоковую)
    $is_cart_screen = false;
    if (function_exists('wc_get_page_id')) {
        $cart_id = wc_get_page_id('cart');
        if ($cart_id && get_queried_object_id() === (int) $cart_id) {
            $is_cart_screen = true;
        }
    }
    if (!$is_cart_screen && function_exists('has_block')) {
        global $post;
        if ($post && has_block('woocommerce/cart', $post)) {
            $is_cart_screen = true;
        }
    }
    if (!$is_cart_screen) return $block_content;
    if (!class_exists('WC') || !WC()->cart) return $block_content;

    // На странице корзины всегда скрываем блок "Новинки" независимо от состояния корзины
    $is_new_block = (!empty($block['blockName']) && $block['blockName'] === 'woocommerce/product-new')
        || tema_souz_block_contains($block, 'woocommerce/product-new');
    if ($is_new_block) { return ''; }

    // Ниже — скрываем любые товарные блоки при пустой корзине
    if (!WC()->cart->is_empty()) return $block_content;
    $bn = isset($block['blockName']) ? (string) $block['blockName'] : '';
    $hide = false;
    if (strpos($bn, 'woocommerce/product-') === 0) { $hide = true; }
    if ($bn === 'woocommerce/products' || $bn === 'woocommerce/product-query' || $bn === 'woocommerce/all-products') { $hide = true; }
    if ($hide) { return ''; }
    return $block_content;
}, 10, 2);

// Для классической корзины: убрать кросс-селлы при пустой корзине
add_action('wp', function () {
    if (function_exists('is_cart') && is_cart() && class_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
        remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
        add_filter('woocommerce_cross_sells_total', '__return_zero');
    }
});

// Блоковая корзина: скрыть блок "Новинки в магазине" через фильтр рендера конкретного блока
add_filter('render_block_woocommerce/product-new', function ($block_content, $block) {
    if (function_exists('wc_get_page_id')) {
        $cart_id = wc_get_page_id('cart');
        if ($cart_id && (int) get_queried_object_id() === (int) $cart_id) {
            return '';
        }
    }
    return $block_content;
}, 10, 2);

// Удаляем заголовок рядом с блоком новинок: core/heading с текстом "Новинки" на странице корзины
add_filter('render_block_core/heading', function ($block_content, $block) {
    if (!function_exists('wc_get_page_id')) return $block_content;
    $cart_id = wc_get_page_id('cart');
    if (!$cart_id || (int) get_queried_object_id() !== (int) $cart_id) return $block_content;
    $text = trim(wp_strip_all_tags($block_content));
    if ($text !== '' && (stripos($text, 'новинк') !== false)) {
        return '';
    }
    return $block_content;
}, 10, 2);

// Резерв: вырезать конкретный заголовок из HTML контента страницы корзины
add_filter('the_content', function ($content) {
    if (!function_exists('wc_get_page_id')) return $content;
    $cart_id = wc_get_page_id('cart');
    if (!$cart_id || (int) get_queried_object_id() !== (int) $cart_id) return $content;
    // Удаляем h2 с классом wp-block-heading и текстом "Новинка в магазине"/"Новинки в магазине"
    $pattern = '~<h2[^>]*class=(["\"])([^"\"]*\bwp-block-heading\b[^"\"]*)\1[^>]*>\s*(Новинк[аи]\s+в\s+магазине)\s*</h2>~iu';
    return preg_replace($pattern, '', $content);
}, 20);

// AJAX: добавить вариативный товар по ID, автоматически выбрав дефолтную/первую доступную вариацию
add_action('wc_ajax_add_parent_to_cart', 'tema_souz_add_parent_to_cart');
add_action('wc_ajax_nopriv_add_parent_to_cart', 'tema_souz_add_parent_to_cart');
function tema_souz_add_parent_to_cart() {
    if (function_exists('wc_load_cart')) { wc_load_cart(); }
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $quantity   = isset($_POST['quantity']) ? max(1, absint($_POST['quantity'])) : 1;
    if (!$product_id) { wp_send_json_error(['error' => true, 'message' => 'No product_id']); }
    $product = wc_get_product($product_id);
    if (!$product) { wp_send_json_error(['error' => true, 'message' => 'Invalid product']); }

    // Для простых товаров добавляем напрямую
    if ($product->is_type('simple')) {
        $cart_item_data = [];
        if (!empty($_POST['unit'])) { $cart_item_data['unit'] = sanitize_text_field($_POST['unit']); }
        $added = WC()->cart->add_to_cart($product_id, $quantity, 0, [], $cart_item_data);
        if ($added) { if (class_exists('WC_AJAX')) { WC_AJAX::get_refreshed_fragments(); } else { wp_send_json_success(['added'=>true]); } }
        wp_send_json_error(['error' => true, 'message' => 'Cannot add simple product', 'product_id' => $product_id]);
    }

    // Для вариативных: берём дефолтную вариацию, иначе ПЕРВУЮ попавшуюся (без проверок наличия)
    if ($product->is_type('variable')) {
        $variation_id = 0;
        $attributes   = [];

        // 1) Сначала строго используем значения по умолчанию товара
        $defaults = $product->get_default_attributes();
        if (!empty($defaults) && function_exists('wc_get_matching_product_variation')) {
            $variation_id = wc_get_matching_product_variation($product, $defaults);
            if ($variation_id) {
                foreach ($defaults as $k => $v) { $attributes['attribute_' . $k] = $v; }
            }
        }

        // 2) Если по дефолтам не нашлось — соберём из «характеристик» (назначенных термов товара)
        if (!$variation_id) {
            $parent_attrs = $product->get_attributes();
            if (!empty($parent_attrs)) {
                $char_attrs = [];
                foreach ($parent_attrs as $pa) {
                    if (!$pa->get_variation()) continue; // только вариативные
                    $name = $pa->get_name(); // pa_*
                    if ($pa->is_taxonomy()) {
                        $slugs = wp_get_post_terms($product->get_id(), $name, ['fields' => 'slugs']);
                        if (!is_wp_error($slugs) && !empty($slugs)) {
                            $char_attrs['attribute_' . $name] = (string) $slugs[0];
                        }
                    }
                }
                if (!empty($char_attrs) && function_exists('wc_get_matching_product_variation')) {
                    $try_variation = wc_get_matching_product_variation($product, $char_attrs);
                    if ($try_variation) {
                        $variation_id = $try_variation;
                        $attributes   = $char_attrs;
                    }
                }
            }
        }
        if (!$variation_id) {
            $children = $product->get_children();
            if (!empty($children)) {
                $variation_id = (int) $children[0];
                $v = wc_get_product($variation_id);
                if ($v) {
                    $v_attrs = $v->get_attributes();
                    if (is_array($v_attrs)) {
                        foreach ($v_attrs as $k => $val) {
                            $attributes[strpos($k, 'attribute_') === 0 ? $k : 'attribute_' . $k] = $val;
                        }
                    }
                }
            } else {
                // Нет вариаций вовсе — создаём базовую вариацию на лету
                $variation_id = wp_insert_post([
                    'post_title'  => $product->get_name() . ' – авто-вариация',
                    'post_status' => 'publish',
                    'post_parent' => $product->get_id(),
                    'post_type'   => 'product_variation',
                    'menu_order'  => 0,
                ]);
                if (!is_wp_error($variation_id) && $variation_id) {
                    update_post_meta($variation_id, '_regular_price', '0');
                    update_post_meta($variation_id, '_price', '0');
                    update_post_meta($variation_id, '_stock_status', 'instock');
                    update_post_meta($variation_id, '_manage_stock', 'no');
                    // Пустые атрибуты (любой)
                    $prod_attrs = $product->get_attributes();
                    foreach ($prod_attrs as $pa) {
                        if (!$pa->get_variation()) continue;
                        $name = $pa->get_name();
                        update_post_meta($variation_id, 'attribute_' . $name, '');
                    }
                } else {
                    $variation_id = 0;
                }
            }
        }
        if ($variation_id) {
            // Форсируем успешную валидацию/доступность на время добавления
            add_filter('woocommerce_add_to_cart_validation', '__return_true', 99, 3);
            add_filter('woocommerce_is_purchasable', '__return_true', 99, 2);
            add_filter('woocommerce_variation_is_purchasable', '__return_true', 99, 2);
            add_filter('woocommerce_product_is_in_stock', '__return_true', 99, 2);
            add_filter('woocommerce_variation_is_in_stock', '__return_true', 99, 2);

            // Точный набор attribute_* берём из Woo helper
            $normalized = function_exists('wc_get_product_variation_attributes')
                ? wc_get_product_variation_attributes($variation_id)
                : [];
            // Удаляем пустые ("любой") значения
            if (!empty($normalized)) {
                foreach ($normalized as $k => $v) {
                    if ($v === '' || $v === null) { unset($normalized[$k]); }
                }
            }
            // Если атрибутов нет (или все пустые) – подставим значения по умолчанию товара
            if (empty($normalized)) {
                $defs = $product->get_default_attributes();
                if (!empty($defs)) {
                    foreach ($defs as $k => $v) { $normalized['attribute_' . $k] = (string) $v; }
                    // убедимся, что variation_id соответствует дефолтам
                    if (function_exists('wc_get_matching_product_variation')) {
                        $match = wc_get_matching_product_variation($product, $normalized);
                        if ($match) { $variation_id = $match; }
                    }
                }
            }

            // Гарантируем цену и наличие у вариации
            $var_obj = wc_get_product($variation_id);
            if ($var_obj && $var_obj->is_type('variation')) {
                if ($var_obj->get_price() === '' || $var_obj->get_price() === null) {
                    if (method_exists($var_obj, 'set_regular_price')) $var_obj->set_regular_price('0');
                    if (method_exists($var_obj, 'set_price')) $var_obj->set_price('0');
                }
                if (method_exists($var_obj, 'set_manage_stock')) $var_obj->set_manage_stock(false);
                if (method_exists($var_obj, 'set_stock_status')) $var_obj->set_stock_status('instock');
                $var_obj->save();
            }
            // Родитель тоже считаем доступным
            if (method_exists($product, 'set_stock_status')) { $product->set_stock_status('instock'); $product->save(); }

            // Пытаемся добавить как родитель + атрибуты
            $cart_item_data = [];
            if (!empty($_POST['unit'])) { $cart_item_data['unit'] = sanitize_text_field($_POST['unit']); }
            $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $normalized, $cart_item_data);

            // Снимаем фильтры
            remove_filter('woocommerce_add_to_cart_validation', '__return_true', 99);
            remove_filter('woocommerce_is_purchasable', '__return_true', 99);
            remove_filter('woocommerce_variation_is_purchasable', '__return_true', 99);
            remove_filter('woocommerce_product_is_in_stock', '__return_true', 99);
            remove_filter('woocommerce_variation_is_in_stock', '__return_true', 99);

            if ($added) { if (class_exists('WC_AJAX')) { WC_AJAX::get_refreshed_fragments(); } else { wp_send_json_success(['added'=>true]); } }

            // Фоллбек: добавляем напрямую вариацию как товар (без attribute_*)
            $cart_item_data = [];
            if (!empty($_POST['unit'])) { $cart_item_data['unit'] = sanitize_text_field($_POST['unit']); }
            $fallback = WC()->cart->add_to_cart($variation_id, $quantity, 0, [], $cart_item_data);
            if ($fallback) { if (class_exists('WC_AJAX')) { WC_AJAX::get_refreshed_fragments(); } else { wp_send_json_success(['added'=>true,'fallback'=>true]); } }

            wp_send_json_error(['error' => true, 'message' => 'Cannot add variable product', 'product_id' => $product_id, 'variation_id' => $variation_id, 'attributes' => $normalized]);
        }
        wp_send_json_error(['error' => true, 'message' => 'No variation found', 'product_id' => $product_id]);
    }

    // По умолчанию
    wp_send_json_error(['error' => true, 'message' => 'Unsupported product type', 'type' => $product ? $product->get_type() : 'unknown']);
}

// Показ единицы измерения в корзине и на чекауте
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (!empty($cart_item['unit'])) {
        $item_data[] = [
            'key'   => __('Единица', 'tema-souz'),
            'value' => wc_clean($cart_item['unit']),
            'display' => wc_clean($cart_item['unit']),
        ];
    }
    return $item_data;
}, 10, 2);

// Сохранение единицы измерения в заказе
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    if (!empty($values['unit'])) {
        $item->add_meta_data(__('Единица', 'tema-souz'), wc_clean($values['unit']), true);
    }
}, 10, 4);

// Подхватить unit из POST/REQUEST при добавлении через стандартную форму на странице товара или AJAX
add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id, $variation_id) {
    // Проверяем и POST и REQUEST для поддержки AJAX
    if (isset($_POST['unit'])) {
        $cart_item_data['unit'] = sanitize_text_field(wp_unslash($_POST['unit']));
    } elseif (isset($_REQUEST['unit'])) {
        $cart_item_data['unit'] = sanitize_text_field(wp_unslash($_REQUEST['unit']));
    }
    return $cart_item_data;
}, 10, 3);

// Русификация единиц измерения в корзине
add_filter('woocommerce_get_item_data', function($item_data, $cart_item) {
    foreach ($item_data as &$item) {
        if (isset($item['key']) && $item['key'] === __('Единица', 'tema-souz')) {
            $map = ['kg' => 'КГ', 'кг' => 'КГ', 'м' => 'М', 'm' => 'М', 'т' => 'Т', 't' => 'Т'];
            $val = mb_strtolower(trim($item['value']));
            if (isset($map[$val])) {
                $item['value'] = $map[$val];
                $item['display'] = $map[$val];
            }
        }
    }
    return $item_data;
}, 20, 2);

// Глобально: если цена 0, выводить "ПО ЗАПРОСУ" вместо цены
add_filter('woocommerce_get_price_html', function ($price_html, $product) {
    if (!$product) return $price_html;
    // Вариативный товар: если минимальная и максимальная цены вариаций 0 → "ПО ЗАПРОСУ"
    if ($product->is_type('variable')) {
        $min = (float) $product->get_variation_price('min', true);
        $max = (float) $product->get_variation_price('max', true);
        if ($min === 0.0 && $max === 0.0) {
            return '<span class="price price--on-request">ПО ЗАПРОСУ</span>';
        }
    } else {
        $price = (float) $product->get_price();
        if ($price === 0.0) {
            return '<span class="price price--on-request">ПО ЗАПРОСУ</span>';
        }
    }
    return $price_html;
}, 10, 2);

// Замена цены товара в корзине на "ПО ЗАПРОСУ" если цена 0
add_filter('woocommerce_cart_item_subtotal', function($subtotal, $cart_item, $cart_item_key) {
    $product = $cart_item['data'];
    if ($product && (float)$product->get_price() === 0.0) {
        return '<span class="price price--on-request">ПО ЗАПРОСУ</span>';
    }
    return $subtotal;
}, 10, 3);

// Замена итоговой суммы корзины на "ПО ЗАПРОСУ" если все товары с нулевой ценой
add_filter('woocommerce_cart_subtotal', function($subtotal, $compound, $cart) {
    if ($cart->get_subtotal() == 0) {
        return '<span class="price price--on-request">ПО ЗАПРОСУ</span>';
    }
    return $subtotal;
}, 10, 3);

// Замена общей суммы заказа на "ПО ЗАПРОСУ"
add_filter('woocommerce_cart_totals_order_total_html', function($value) {
    if (WC()->cart && WC()->cart->get_total('') == 0) {
        return '<span class="price price--on-request">ПО ЗАПРОСУ</span>';
    }
    return $value;
}, 10, 1);

// Фильтрация товаров по атрибутам через GET-параметры filter_pa_*
add_action('pre_get_posts', function ($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if (!(function_exists('is_product_category') && is_product_category())) return;

    $tax_query = (array) $q->get('tax_query');
    foreach ($_GET as $key => $value) {
        if (strpos($key, 'filter_pa_') === 0) {
            $taxonomy = str_replace('filter_', '', sanitize_key($key)); // pa_xxx
            if (!taxonomy_exists($taxonomy)) continue;
            $values = array_map('sanitize_title', (array) $value);
            if (!empty($values)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $values,
                    'operator' => 'IN',
                ];
            }
        }
    }
    if (!empty($tax_query)) {
        $q->set('tax_query', $tax_query);
    }
});

// Разрешить загрузку локальных шрифтов
add_filter('upload_mimes', function ($mimes) {
    $mimes['woff'] = 'font/woff';
    $mimes['woff2'] = 'font/woff2';
    return $mimes;
});

// Подключение Carbon Fields
add_action('after_setup_theme', function () {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
    if (class_exists('Carbon_Fields\Carbon_Fields')) {
        \Carbon_Fields\Carbon_Fields::boot();
    }
});

// Регистрация полей Carbon Fields (базовые настройки темы)
add_action('carbon_fields_register_fields', function () {
    if (!class_exists('Carbon_Fields\Container')) return;

    // ========================================================================
    // КОНТАКТНЫЕ ДАННЫЕ (отдельный контейнер)
    // ========================================================================
    \Carbon_Fields\Container::make('theme_options', __('Контактные данные', 'tema-souz'))
        ->set_page_parent('themes.php')
        ->add_fields([
            \Carbon_Fields\Field::make('complex', 'site_phones', 'Телефоны')
                ->setup_labels(['plural_name' => 'Телефоны', 'singular_name' => 'Телефон'])
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'phone', 'Номер')->set_attribute('placeholder', '+7 (921) 390-35-09'),
                ])
                ->set_help_text('Используются в шапке, подвале и блоке контактов по всему сайту'),
            \Carbon_Fields\Field::make('text', 'site_email', 'E-mail')
                ->set_attribute('placeholder', 's_metallist@mail.ru')
                ->set_help_text('Используется в шапке (с кнопкой копирования), подвале и блоке контактов'),
            \Carbon_Fields\Field::make('text', 'site_address', 'Адрес')
                ->set_attribute('placeholder', '194100, г Санкт-Петербург...')
                ->set_help_text('Используется в блоке контактов по всему сайту'),

            // ── Яндекс Карта ──────────────────────────────────────────────
            \Carbon_Fields\Field::make('separator', 'sep_map', 'Яндекс Карта'),
            \Carbon_Fields\Field::make('text', 'contacts_map_lat', 'Широта (lat)')
                ->set_attribute('placeholder', '60.0126')
                ->set_default_value('60.0126'),
            \Carbon_Fields\Field::make('text', 'contacts_map_lng', 'Долгота (lng)')
                ->set_attribute('placeholder', '30.3135')
                ->set_default_value('30.3135'),
            \Carbon_Fields\Field::make('text', 'contacts_map_zoom', 'Масштаб (zoom)')
                ->set_attribute('placeholder', '15')
                ->set_default_value('15'),
            \Carbon_Fields\Field::make('text', 'contacts_map_balloon_title', 'Заголовок балуна')
                ->set_attribute('placeholder', 'СОЮЗ-МЕТАЛЛИСТ')
                ->set_default_value('СОЮЗ-МЕТАЛЛИСТ'),
            \Carbon_Fields\Field::make('textarea', 'contacts_map_balloon_content', 'Текст балуна')
                ->set_rows(2)
                ->set_help_text('Если пусто — подставляется адрес'),
            \Carbon_Fields\Field::make('image', 'contacts_map_icon', 'Иконка метки')
                ->set_help_text('Кастомная иконка для метки на карте (необязательно)'),
        ]);

    // ========================================================================
    // НАСТРОЙКИ ТЕМЫ
    // ========================================================================
    $container = \Carbon_Fields\Container::make('theme_options', __('Настройки темы', 'tema-souz'))
        ->add_tab(__('Общие', 'tema-souz'), [
            \Carbon_Fields\Field::make('text', 'tema_souz_yamaps_api_key', 'Yandex Maps API Key'),
        ])
        ->add_tab(__('Главная', 'tema-souz'), [
            \Carbon_Fields\Field::make('image', 'tema_souz_hero_front', 'HERO изображение: Главная'),
            \Carbon_Fields\Field::make('complex', 'hero_teaser_slides', 'HERO: Слайды-тизеры')
                ->setup_labels([ 'plural_name' => 'Слайды', 'singular_name' => 'Слайд' ])
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'subtitle', 'Заголовок')->set_required(true),
                    \Carbon_Fields\Field::make('textarea', 'text', 'Текст')->set_rows(3),
                ])
                ->set_help_text('Слайды в hero-секции на главной странице'),
            \Carbon_Fields\Field::make('complex', 'about_settings', 'Блок «О нас»')
                ->set_max(1)
                ->setup_labels([ 'plural_name' => 'Блок', 'singular_name' => 'Блок' ])
                ->add_fields([
                    \Carbon_Fields\Field::make('rich_text', 'about_text_1', 'О нас: текст 1'),
                    \Carbon_Fields\Field::make('rich_text', 'about_text_2', 'О нас: текст 2'),
                    \Carbon_Fields\Field::make('complex', 'about_slides', 'Слайды «О нас»')
                        ->setup_labels([ 'plural_name' => 'Слайды', 'singular_name' => 'Слайд' ])
                        ->add_fields([
                            \Carbon_Fields\Field::make('image', 'image', 'Изображение'),
                            \Carbon_Fields\Field::make('text', 'caption', 'Подпись (необязательно)'),
                        ]),
                ]),
            \Carbon_Fields\Field::make('complex', 'front_map_points', 'Точки на карте')
                ->setup_labels([ 'plural_name' => 'Точки', 'singular_name' => 'Точка' ])
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'position_top', 'Позиция сверху (%)')->set_attribute('type', 'number')->set_help_text('Например: 35.5'),
                    \Carbon_Fields\Field::make('text', 'position_left', 'Позиция слева (%)')->set_attribute('type', 'number')->set_help_text('Например: 62'),
                    \Carbon_Fields\Field::make('text', 'city', 'Город'),
                    \Carbon_Fields\Field::make('text', 'description', 'Краткое описание'),
                ]),
            \Carbon_Fields\Field::make('complex', 'geography_features', 'Текст под картой географии')
                ->setup_labels([ 'plural_name' => 'Пункты', 'singular_name' => 'Пункт' ])
                ->set_max(3)
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'text', 'Текст пункта'),
                ]),
            \Carbon_Fields\Field::make('complex', 'home_stats', 'Блок «Статистика»')
                ->set_max(1)
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'main_caption', 'Подпись главного показателя')->set_default_value('ТЫСЯЧ ТОНН\nМЕТАЛЛА ПРОДАНО'),
                    \Carbon_Fields\Field::make('text', 'main_value', 'Главное значение')->set_default_value('753'),
                    \Carbon_Fields\Field::make('text', 'area_caption', 'Подпись: площадь складов')->set_default_value('ОБЩАЯ\nПЛОЩАДЬ СКЛАДОВ'),
                    \Carbon_Fields\Field::make('text', 'area_value', 'Значение: площадь')->set_default_value('950м²'),
                    \Carbon_Fields\Field::make('text', 'years_caption', 'Подпись: лет на рынке')->set_default_value('ЛЕТ\nНА РЫНКЕ'),
                    \Carbon_Fields\Field::make('text', 'years_value', 'Значение: лет на рынке')->set_default_value('20'),
                    \Carbon_Fields\Field::make('text', 'staff_caption', 'Подпись: сотрудников')->set_default_value('СОТРУДНИКОВ\nВ ШТАТЕ'),
                    \Carbon_Fields\Field::make('text', 'staff_value', 'Значение: сотрудников')->set_default_value('253'),
                ]),
            \Carbon_Fields\Field::make('complex', 'home_partners', 'Партнеры (логотипы)')
                ->add_fields([
                    \Carbon_Fields\Field::make('image', 'logo', 'Логотип')->set_required(true),
                    \Carbon_Fields\Field::make('text', 'url', 'Ссылка (необязательно)'),
                    \Carbon_Fields\Field::make('text', 'title', 'Название (alt)')->set_help_text('Текст для alt'),
                ]),
        ])
        ->add_tab(__('О компании', 'tema-souz'), [
            \Carbon_Fields\Field::make('image', 'tema_souz_hero_about', 'HERO изображение: О компании'),
        ])
        ->add_tab(__('Оптовым клиентам', 'tema-souz'), [
            \Carbon_Fields\Field::make('image', 'tema_souz_hero_b2b', 'HERO изображение: Оптовым клиентам'),
        ])
        ->add_tab(__('Контакты', 'tema-souz'), [
            \Carbon_Fields\Field::make('image', 'tema_souz_hero_contacts', 'HERO изображение: Контакты'),
            \Carbon_Fields\Field::make('text', 'contacts_hero_title', 'Заголовок геро-секции')->set_default_value('КОНТАКТЫ'),
            \Carbon_Fields\Field::make('text', 'contacts_address', 'Адрес')->set_default_value('194100, г Санкт-Петербург, Выборгский р-н, ул Александра Матросова, д 4 к 2 литера Е, помещ 1-Н'),
            \Carbon_Fields\Field::make('complex', 'contacts_phones', 'Телефоны')->add_fields([\Carbon_Fields\Field::make('text', 'phone', 'Телефон')]),
            \Carbon_Fields\Field::make('text', 'contacts_email', 'E-mail')->set_default_value('s_metallist@mail.ru'),
            \Carbon_Fields\Field::make('text', 'contacts_map_dom_id', 'ID контейнера карты')->set_default_value('contacts-map'),
            \Carbon_Fields\Field::make('text', 'contacts_form_shortcode', 'Шорткод формы обратной связи')->set_help_text('Вставьте шорткод Contact Form 7, например: [contact-form-7 id="123" title="Контактная форма"]'),
            \Carbon_Fields\Field::make('text', 'contacts_map_lat', 'Широта (latitude)')->set_default_value('60.0126')->set_help_text('Координата широты для центра карты'),
            \Carbon_Fields\Field::make('text', 'contacts_map_lng', 'Долгота (longitude)')->set_default_value('30.3135')->set_help_text('Координата долготы для центра карты'),
            \Carbon_Fields\Field::make('text', 'contacts_map_zoom', 'Масштаб карты')->set_default_value('15')->set_help_text('Уровень масштабирования от 1 до 19'),
            \Carbon_Fields\Field::make('image', 'contacts_map_icon', 'Иконка локации')->set_help_text('Загрузите свою иконку для маркера на карте'),
            \Carbon_Fields\Field::make('text', 'contacts_map_balloon_title', 'Заголовок балуна')->set_default_value('СОЮЗ-МЕТАЛЛИСТ'),
            \Carbon_Fields\Field::make('textarea', 'contacts_map_balloon_content', 'Содержимое балуна')->set_default_value('194100, г Санкт-Петербург, Выборгский р-н, ул Александра Матросова, д 4 к 2 литера Е, помещ 1-Н'),
        ])
        ->add_tab(__('Архив услуг', 'tema-souz'), [
            \Carbon_Fields\Field::make('image', 'tema_souz_hero_services_archive', 'HERO изображение: Архив услуг'),
        ])
        ->add_tab(__('Архив новостей', 'tema-souz'), [
            \Carbon_Fields\Field::make('image', 'tema_souz_hero_news_archive', 'HERO изображение: Архив новостей'),
        ])
        ->add_tab(__('Категории каталога', 'tema-souz'), [
            \Carbon_Fields\Field::make('text', 'shop_hero_title', 'Заголовок HERO: Магазин')->set_default_value('КАТАЛОГ ПРОДУКЦИИ'),
            \Carbon_Fields\Field::make('image', 'shop_hero_image', 'HERO изображение: Магазин'),
        ])
        ->add_tab(__('Карточка товара', 'tema-souz'), [
            \Carbon_Fields\Field::make('complex', 'product_advantages', 'Преимущества на карточке товара')
                ->setup_labels([ 'plural_name' => 'Пункты', 'singular_name' => 'Пункт' ])
                ->add_fields([
                    \Carbon_Fields\Field::make('image', 'icon', 'Иконка')->set_help_text('SVG/PNG'),
                    \Carbon_Fields\Field::make('text', 'title', 'Заголовок')->set_required(true),
                    \Carbon_Fields\Field::make('text', 'description', 'Описание')->set_required(true),
                ]),
            \Carbon_Fields\Field::make('rich_text', 'product_about_text', 'Текст блока «СОЮЗ-МЕТАЛЛИСТ»')
                ->set_help_text('Отображается под заголовком «СОЮЗ-МЕТАЛЛИСТ» на карточке товара'),
        ]);

    // Поля: страница О компании
    \Carbon_Fields\Container::make('post_meta', __('О компании: настройки', 'tema-souz'))
        ->where('post_template', '=', 'page-about.php')
        ->add_fields([
            \Carbon_Fields\Field::make('text', 'about_hero_title', 'Заголовок геро-секции')->set_default_value('О КОМПАНИИ'),
            \Carbon_Fields\Field::make('rich_text', 'about_company_text_1', 'Текст компании 1'),
            \Carbon_Fields\Field::make('rich_text', 'about_company_text_2', 'Текст компании 2'),
            \Carbon_Fields\Field::make('rich_text', 'about_company_text_3', 'Текст компании 3'),
            \Carbon_Fields\Field::make('separator', 'sep_about_intro', 'ВВОДНЫЙ БЛОК (текст + фото)'),
            \Carbon_Fields\Field::make('rich_text', 'about_intro_text', 'Текст вводного блока'),
            \Carbon_Fields\Field::make('image', 'about_intro_image', 'Фото (соотношение 1:1)')
                ->set_help_text('Рекомендуемый размер: 800×800 px'),
        ]);

    // Поля: страница B2B
    \Carbon_Fields\Container::make('post_meta', __('B2B: настройки', 'tema-souz'))
        ->where('post_template', '=', 'page-b2b.php')
        ->add_fields([
            \Carbon_Fields\Field::make('text', 'b2b_hero_title', 'Заголовок геро-секции')->set_default_value('ОПТОВЫМ КЛИЕНТАМ'),
            \Carbon_Fields\Field::make('rich_text', 'b2b_description', 'Описание'),
        ]);

    // Поля: страница Литейное производство
    \Carbon_Fields\Container::make('post_meta', __('Литейное производство: настройки', 'tema-souz'))
        ->where('post_template', '=', 'page-foundry.php')
        ->add_fields([

            // ── Hero ──────────────────────────────────────────────────────
            \Carbon_Fields\Field::make('separator', 'sep_hero', 'HERO'),
            \Carbon_Fields\Field::make('text', 'foundry_hero_title', 'Заголовок (можно <br>)')
                ->set_default_value('ЛИТЕЙНОЕ<br>ПРОИЗВОДСТВО'),
            \Carbon_Fields\Field::make('image', 'foundry_hero_bg', 'Фоновое фото Hero'),

            // ── Технология литья ──────────────────────────────────────────
            \Carbon_Fields\Field::make('separator', 'sep_tech', 'ТЕХНОЛОГИЯ ЛИТЬЯ'),
            \Carbon_Fields\Field::make('image', 'foundry_tech_image', 'Фотография (левая колонка)'),
            \Carbon_Fields\Field::make('text', 'foundry_tech_label_title', 'Плашка на фото — подпись')
                ->set_default_value('ТОЧНОСТЬ'),
            \Carbon_Fields\Field::make('text', 'foundry_tech_label_value', 'Плашка на фото — значение')
                ->set_default_value('±0.1 мм'),
            \Carbon_Fields\Field::make('textarea', 'foundry_tech_lead', 'Текст-лид (крупный)')
                ->set_rows(3)
                ->set_default_value('Изготовление отливок осуществляется по технологии литья по выплавляемым моделям — для деталей сложной формы с высокой точностью размеров.'),
            \Carbon_Fields\Field::make('textarea', 'foundry_tech_body', 'Основной текст')
                ->set_rows(3)
                ->set_default_value('Модели отливок изготавливаются по пресс-формам, а также с использованием 3D-печати — это позволяет оперативно запускать производство и изготавливать опытные партии изделий.'),
            \Carbon_Fields\Field::make('complex', 'foundry_tech_methods', 'Методы (2 блока)')
                ->set_max(2)
                ->set_layout('tabbed-horizontal')
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'num', 'Номер')->set_default_value('01'),
                    \Carbon_Fields\Field::make('text', 'label', 'Заголовок метода'),
                    \Carbon_Fields\Field::make('textarea', 'text', 'Описание')->set_rows(2),
                    \Carbon_Fields\Field::make('text', 'tag', 'Тег (напр. УППФ-3М · 2 шт.)'),
                    \Carbon_Fields\Field::make('checkbox', 'active', 'Активный (красный акцент)'),
                ]),

            // ── Оборудование и сплавы ─────────────────────────────────────
            \Carbon_Fields\Field::make('separator', 'sep_equip', 'ОБОРУДОВАНИЕ И СПЛАВЫ'),
            \Carbon_Fields\Field::make('image', 'foundry_equip_photo', 'Фото (левая колонка блока)'),
            \Carbon_Fields\Field::make('complex', 'foundry_alloy_groups', 'Марки сплавов — группы')
                ->set_layout('tabbed-vertical')
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'title', 'Заголовок группы (строки через Enter)'),
                    \Carbon_Fields\Field::make('textarea', 'alloys', 'Марки (каждая с новой строки)'),
                    \Carbon_Fields\Field::make('text', 'param_weight', 'Вес (напр. 0,1–5)'),
                    \Carbon_Fields\Field::make('text', 'param_wall', 'Стенка (напр. 1–10)'),
                    \Carbon_Fields\Field::make('text', 'param_size', 'Макс. размер (напр. 600×150×70)'),
                    \Carbon_Fields\Field::make('checkbox', 'active', 'Активный (красный акцент)'),
                ]),
            \Carbon_Fields\Field::make('complex', 'foundry_equipment', 'Оборудование — строки')
                ->set_layout('tabbed-vertical')
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'model', 'Модель (напр. УППФ-3М)'),
                    \Carbon_Fields\Field::make('text', 'title', 'Название'),
                    \Carbon_Fields\Field::make('text', 'desc', 'Краткое описание (напр. Тигель до 40 кг · 2 шт.)'),
                    \Carbon_Fields\Field::make('text', 'badge', 'Бейдж (напр. ВАКУУМ)'),
                    \Carbon_Fields\Field::make('checkbox', 'active', 'Активный (красный акцент)'),
                ]),

            // ── Постобработка ─────────────────────────────────────────────
            \Carbon_Fields\Field::make('separator', 'sep_post', 'ПОСТОБРАБОТКА'),
            \Carbon_Fields\Field::make('text', 'foundry_post_title', 'Заголовок (можно <br>)')
                ->set_default_value('МЕХАНИЧЕСКАЯ<br>И ТЕРМИЧЕСКАЯ ОБРАБОТКА'),
            \Carbon_Fields\Field::make('textarea', 'foundry_post_text', 'Текст')->set_rows(3),
            \Carbon_Fields\Field::make('complex', 'foundry_post_items', 'Список пунктов')
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'text', 'Пункт'),
                ]),
            \Carbon_Fields\Field::make('image', 'foundry_post_image', 'Фото (правая колонка)'),

            // ── CTA форма ─────────────────────────────────────────────────
            \Carbon_Fields\Field::make('separator', 'sep_cta', 'ФОРМА ЗАЯВКИ'),
            \Carbon_Fields\Field::make('text', 'foundry_cta_title', 'Заголовок (можно <br>)')
                ->set_default_value('ОБСУДИМ<br>ВАШ ПРОЕКТ?'),
            \Carbon_Fields\Field::make('textarea', 'foundry_cta_desc', 'Описание')
                ->set_rows(2)
                ->set_default_value('Наши специалисты подберут оптимальный сплав и технологию под ваши требования.'),
        ]);

    // Поля: страница Контакты — перенесены в настройки темы (вкладка «Контакты»)
});

// Поле HERO на экранах добавления/редактирования категории (product_cat)
add_action('product_cat_add_form_fields', function () {
    ?>
    <div class="form-field term-hero-wrap">
        <label for="product_cat_hero_image">HERO изображение категории</label>
        <div id="product_cat_hero_preview" style="margin-bottom:10px;"></div>
        <input type="hidden" name="product_cat_hero_image" id="product_cat_hero_image" value="" />
        <button type="button" class="button" id="product_cat_hero_upload">Выбрать изображение</button>
        <button type="button" class="button" id="product_cat_hero_remove" style="display:none;">Удалить</button>
    </div>
    <?php
});

add_action('product_cat_edit_form_fields', function ($term) {
    $hero_id = (int) get_term_meta($term->term_id, 'product_cat_hero_image', true);
    $url = $hero_id ? wp_get_attachment_image_url($hero_id, 'medium') : '';
    ?>
    <tr class="form-field term-hero-wrap">
        <th scope="row"><label for="product_cat_hero_image">HERO изображение категории</label></th>
        <td>
            <div id="product_cat_hero_preview" style="margin-bottom:10px;">
                <?php if ($url) { echo '<img src="' . esc_url($url) . '" style="max-width:200px;height:auto;" />'; } ?>
            </div>
            <input type="hidden" name="product_cat_hero_image" id="product_cat_hero_image" value="<?php echo esc_attr($hero_id); ?>" />
            <button type="button" class="button" id="product_cat_hero_upload"><?php echo $url ? 'Заменить изображение' : 'Выбрать изображение'; ?></button>
            <button type="button" class="button" id="product_cat_hero_remove" <?php echo $url ? '' : 'style="display:none;"'; ?>>Удалить</button>
        </td>
    </tr>
    <?php
});

add_action('created_product_cat', function ($term_id) {
    if (isset($_POST['product_cat_hero_image'])) {
        update_term_meta($term_id, 'product_cat_hero_image', (int) $_POST['product_cat_hero_image']);
    }
});

// Собственный импорт товаров WooCommerce
add_action('admin_menu', function () {
    add_management_page(
        'Импорт товаров WooCommerce',
        'Импорт товаров',
        'manage_woocommerce',
        'custom-wc-import',
        'tema_souz_custom_import_page'
    );
    
    // Скрипт полной очистки каталога
    add_management_page(
        'Очистка каталога WooCommerce',
        'Очистка каталога',
        'manage_woocommerce',
        'clear-wc-catalog',
        'tema_souz_clear_catalog_page'
    );
    
    // Скрипт импорта только атрибутов
    add_management_page(
        'Импорт атрибутов товаров',
        'Импорт атрибутов',
        'manage_woocommerce',
        'import-attributes-only',
        'tema_souz_import_attributes_page'
    );
});

function tema_souz_custom_import_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Недостаточно прав.');
    }

    $message = '';
    $error = '';

    if (isset($_POST['split_csv']) && check_admin_referer('custom_import_action')) {
        if (isset($_FILES['csv_files']) && !empty($_FILES['csv_files']['name'][0])) {
            $upload_dir = wp_upload_dir();
            $uploaded_files = [];
            $total_parts = 0;
            
            // Обрабатываем каждый загруженный файл
            for ($i = 0; $i < count($_FILES['csv_files']['name']); $i++) {
                if ($_FILES['csv_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = sanitize_file_name($_FILES['csv_files']['name'][$i]);
                    $file_path = $upload_dir['path'] . '/' . $file_name;
                    
                    if (move_uploaded_file($_FILES['csv_files']['tmp_name'][$i], $file_path)) {
                        $uploaded_files[] = $file_path;
                    }
                }
            }
            
            if (!empty($uploaded_files)) {
                $message = "Загружено файлов: " . count($uploaded_files) . "<br>";
                
                // Проверяем, нужно ли автоматически импортировать части
                $auto_import = isset($_POST['auto_import_parts']) && $_POST['auto_import_parts'] === '1';
                
                if ($auto_import) {
                    $message .= "<br><strong>Начинаем автоматический импорт всех файлов...</strong><br>";
                    $message .= '<div class="import-in-progress" style="margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px;">';
                    $message .= '<div style="background: #ddd; height: 20px; border-radius: 10px; overflow: hidden;">';
                    $message .= '<div class="progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
                    $message .= '</div>';
                    $message .= '<div class="progress-text" style="margin-top: 10px; font-weight: bold;">Подготовка к импорту...</div>';
                    $message .= '</div>';
                    
                    $total_result = [
                        'processed' => 0,
                        'created' => 0,
                        'updated' => 0,
                        'errors' => 0,
                        'attributes_created' => 0,
                        'attributes_assigned' => 0
                    ];
                    
                    $file_count = 0;
                    foreach ($uploaded_files as $file_path) {
                        $file_count++;
                        $filename = basename($file_path);
                        $file_size = filesize($file_path);
                        
                        $message .= "<br>Обрабатываем файл $file_count из " . count($uploaded_files) . ": <strong>$filename</strong> (" . round($file_size/1024/1024, 2) . " MB)<br>";
                        
                        // Проверяем размер файла для выбора функции импорта
                        $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                        
                        if ($use_optimized) {
                            $result = tema_souz_import_products_from_csv_optimized($file_path);
                        } else {
                            $result = tema_souz_import_products_from_csv($file_path);
                        }
                        
                        if ($result['success']) {
                            $total_result['processed'] += $result['processed'];
                            $total_result['created'] += $result['created'];
                            $total_result['updated'] += $result['updated'];
                            $total_result['errors'] += $result['errors'];
                            $total_result['attributes_created'] += $result['attributes_created'];
                            $total_result['attributes_assigned'] += $result['attributes_assigned'];
                            
                            $message .= "✓ Обработано: {$result['processed']}, Создано: {$result['created']}, Ошибок: {$result['errors']}<br>";
                        } else {
                            $message .= "✗ Ошибка импорта: " . $result['error'] . "<br>";
                            $total_result['errors']++;
                        }
                        
                        // Очищаем память между файлами
                        wp_cache_flush();
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                        
                        // Удаляем временный файл
                        unlink($file_path);
                    }
                    
                    $message .= "<br><strong>Итоговый результат импорта:</strong><br>";
                    $message .= "Обработано файлов: $file_count<br>";
                    $message .= "Всего обработано: {$total_result['processed']}<br>";
                    $message .= "Создано товаров: {$total_result['created']}<br>";
                    $message .= "Обновлено товаров: {$total_result['updated']}<br>";
                    $message .= "Ошибок: {$total_result['errors']}<br>";
                    if ($total_result['attributes_created'] > 0) {
                        $message .= "Создано атрибутов: {$total_result['attributes_created']}<br>";
                    }
                    if ($total_result['attributes_assigned'] > 0) {
                        $message .= "Назначено атрибутов: {$total_result['attributes_assigned']}<br>";
                    }
                    
                } else {
                    // Обычный импорт без автоматического разбиения
                    $total_result = [
                        'processed' => 0,
                        'created' => 0,
                        'updated' => 0,
                        'errors' => 0,
                        'attributes_created' => 0,
                        'attributes_assigned' => 0
                    ];
                    
                    foreach ($uploaded_files as $file_path) {
                        $filename = basename($file_path);
                        $file_size = filesize($file_path);
                        
                        $message .= "<br>Импортируем файл: <strong>$filename</strong> (" . round($file_size/1024/1024, 2) . " MB)<br>";
                        
                        // Проверяем размер файла для выбора функции импорта
                        $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                        
                        if ($use_optimized) {
                            $result = tema_souz_import_products_from_csv_optimized($file_path);
                        } else {
                            $result = tema_souz_import_products_from_csv($file_path);
                        }
                        
                        if ($result['success']) {
                            $total_result['processed'] += $result['processed'];
                            $total_result['created'] += $result['created'];
                            $total_result['updated'] += $result['updated'];
                            $total_result['errors'] += $result['errors'];
                            $total_result['attributes_created'] += $result['attributes_created'];
                            $total_result['attributes_assigned'] += $result['attributes_assigned'];
                            
                            $message .= "✓ Обработано: {$result['processed']}, Создано: {$result['created']}, Ошибок: {$result['errors']}<br>";
                        } else {
                            $message .= "✗ Ошибка импорта: " . $result['error'] . "<br>";
                            $total_result['errors']++;
                        }
                        
                        // Очищаем память между файлами
                        wp_cache_flush();
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                        
                        // Удаляем временный файл
                        unlink($file_path);
                    }
                    
                    $message .= "<br><strong>Итоговый результат импорта:</strong><br>";
                    $message .= "Обработано файлов: " . count($uploaded_files) . "<br>";
                    $message .= "Всего обработано: {$total_result['processed']}<br>";
                    $message .= "Создано товаров: {$total_result['created']}<br>";
                    $message .= "Обновлено товаров: {$total_result['updated']}<br>";
                    $message .= "Ошибок: {$total_result['errors']}<br>";
                    if ($total_result['attributes_created'] > 0) {
                        $message .= "Создано атрибутов: {$total_result['attributes_created']}<br>";
                    }
                    if ($total_result['attributes_assigned'] > 0) {
                        $message .= "Назначено атрибутов: {$total_result['attributes_assigned']}<br>";
                    }
                }
            } else {
                $error = 'Не удалось загрузить файлы';
            }
        } else {
            $error = 'Выберите файлы для импорта';
        }
    } elseif (isset($_POST['import_products']) && check_admin_referer('custom_import_action')) {
        if (isset($_FILES['csv_files']) && !empty($_FILES['csv_files']['name'][0])) {
            $upload_dir = wp_upload_dir();
            $uploaded_files = [];
            
            // Обрабатываем каждый загруженный файл
            for ($i = 0; $i < count($_FILES['csv_files']['name']); $i++) {
                if ($_FILES['csv_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = sanitize_file_name($_FILES['csv_files']['name'][$i]);
                    $file_path = $upload_dir['path'] . '/' . $file_name;
                    
                    if (move_uploaded_file($_FILES['csv_files']['tmp_name'][$i], $file_path)) {
                        $uploaded_files[] = $file_path;
                    }
                }
            }
            
            if (!empty($uploaded_files)) {
                $total_result = [
                    'processed' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'errors' => 0,
                    'attributes_created' => 0,
                    'attributes_assigned' => 0
                ];
                
                foreach ($uploaded_files as $file_path) {
                    $filename = basename($file_path);
                    $file_size = filesize($file_path);
                    
                    $message .= "<br>Импортируем файл: <strong>$filename</strong> (" . round($file_size/1024/1024, 2) . " MB)<br>";
                    
                    // Проверяем размер файла для выбора функции импорта
                    $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                    
                    if ($use_optimized) {
                        $result = tema_souz_import_products_from_csv_optimized($file_path);
                    } else {
                        $result = tema_souz_import_products_from_csv($file_path);
                    }
                    
                    if ($result['success']) {
                        $total_result['processed'] += $result['processed'];
                        $total_result['created'] += $result['created'];
                        $total_result['updated'] += $result['updated'];
                        $total_result['errors'] += $result['errors'];
                        $total_result['attributes_created'] += $result['attributes_created'];
                        $total_result['attributes_assigned'] += $result['attributes_assigned'];
                        
                        $message .= "✓ Обработано: {$result['processed']}, Создано: {$result['created']}, Ошибок: {$result['errors']}<br>";
                    } else {
                        $message .= "✗ Ошибка импорта: " . $result['error'] . "<br>";
                        $total_result['errors']++;
                    }
                    
                    // Очищаем память между файлами
                    wp_cache_flush();
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                    
                    // Удаляем временный файл
                    unlink($file_path);
                }
                
                $message = "Импорт завершен. Обработано файлов: " . count($uploaded_files) . "<br>";
                $message .= "Всего обработано: {$total_result['processed']}, Создано: {$total_result['created']}, Обновлено: {$total_result['updated']}, Ошибок: {$total_result['errors']}";
                if (isset($total_result['attributes_created']) && $total_result['attributes_created'] > 0) {
                    $message .= ", Создано атрибутов: {$total_result['attributes_created']}";
                }
                if (isset($total_result['attributes_assigned']) && $total_result['attributes_assigned'] > 0) {
                    $message .= ", Назначено атрибутов: {$total_result['attributes_assigned']}";
                }
            } else {
                $error = 'Не удалось загрузить файлы';
            }
        } else {
            $error = 'Выберите файлы для импорта';
        }
    } elseif (isset($_POST['split_csv']) && check_admin_referer('custom_import_action')) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file'];
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['path'] . '/' . sanitize_file_name($file['name']);
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $split_result = tema_souz_split_large_csv($file_path, 1000);
                if ($split_result['success']) {
                    $file_count = count($split_result['files']);
                    $message = "Файл успешно разбит на $file_count частей:<br>";
                    
                    // Проверяем, нужно ли автоматически импортировать части
                    $auto_import = isset($_POST['auto_import_parts']) && $_POST['auto_import_parts'] === '1';
                    
                    if ($auto_import) {
                        $message .= "<br><strong>Начинаем автоматический импорт всех частей...</strong><br>";
                        $message .= '<div class="import-in-progress" style="margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px;">';
                        $message .= '<div style="background: #ddd; height: 20px; border-radius: 10px; overflow: hidden;">';
                        $message .= '<div class="progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
                        $message .= '</div>';
                        $message .= '<div class="progress-text" style="margin-top: 10px; font-weight: bold;">Подготовка к импорту...</div>';
                        $message .= '</div>';
                        
                        $total_result = [
                            'processed' => 0,
                            'created' => 0,
                            'updated' => 0,
                            'errors' => 0,
                            'attributes_created' => 0,
                            'attributes_assigned' => 0
                        ];
                        
                        foreach ($split_result['files'] as $index => $split_file) {
                            $filename = basename($split_file);
                            $part_number = $index + 1;
                            $message .= "<br>Импортируем часть $part_number из $file_count: <strong>$filename</strong><br>";
                            
                            // Записываем прогресс
                            tema_souz_write_import_progress(
                                $part_number, 
                                $file_count, 
                                $total_result['created'], 
                                $total_result['updated'], 
                                $total_result['errors']
                            );
                            
                            // Импортируем часть
                            $part_result = tema_souz_import_products_from_csv_optimized($split_file);
                            
                            if ($part_result['success']) {
                                $total_result['processed'] += $part_result['processed'];
                                $total_result['created'] += $part_result['created'];
                                $total_result['updated'] += $part_result['updated'];
                                $total_result['errors'] += $part_result['errors'];
                                $total_result['attributes_created'] += $part_result['attributes_created'];
                                $total_result['attributes_assigned'] += $part_result['attributes_assigned'];
                                
                                $message .= "✓ Обработано: {$part_result['processed']}, Создано: {$part_result['created']}, Ошибок: {$part_result['errors']}<br>";
                            } else {
                                $message .= "✗ Ошибка импорта: " . $part_result['error'] . "<br>";
                                $total_result['errors']++;
                            }
                            
                            // Очищаем память между частями
                            wp_cache_flush();
                            if (function_exists('gc_collect_cycles')) {
                                gc_collect_cycles();
                            }
                            
                            // Небольшая пауза для стабильности
                            usleep(100000); // 0.1 секунды
                        }
                        
                        $message .= "<br><strong>Итоговый результат импорта:</strong><br>";
                        $message .= "Всего обработано: {$total_result['processed']}<br>";
                        $message .= "Создано товаров: {$total_result['created']}<br>";
                        $message .= "Обновлено товаров: {$total_result['updated']}<br>";
                        $message .= "Ошибок: {$total_result['errors']}<br>";
                        if ($total_result['attributes_created'] > 0) {
                            $message .= "Создано атрибутов: {$total_result['attributes_created']}<br>";
                        }
                        if ($total_result['attributes_assigned'] > 0) {
                            $message .= "Назначено атрибутов: {$total_result['attributes_assigned']}<br>";
                        }
                        
                        // Удаляем временные файлы частей
                        foreach ($split_result['files'] as $split_file) {
                            if (file_exists($split_file)) {
                                unlink($split_file);
                            }
                        }
                        $message .= "<br>Временные файлы частей удалены.";
                        
                        // Очищаем файл прогресса
                        tema_souz_clear_import_progress();
                        
                    } else {
                        foreach ($split_result['files'] as $split_file) {
                            $filename = basename($split_file);
                            $message .= "• <a href='" . $upload_dir['url'] . "/$filename' target='_blank'>$filename</a><br>";
                        }
                        $message .= "<br>Теперь вы можете импортировать каждый файл отдельно.";
                    }
                } else {
                    $error = "Ошибка разбиения файла: " . $split_result['error'];
                }
            } else {
                $error = 'Не удалось загрузить файл';
            }
        } else {
            $error = 'Выберите файл для разбиения';
        }
    } elseif (isset($_POST['import_products']) && check_admin_referer('custom_import_action')) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file'];
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['path'] . '/' . sanitize_file_name($file['name']);
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Проверяем размер файла для выбора функции импорта
                $file_size = filesize($file_path);
                $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                
                if ($use_optimized) {
                    $result = tema_souz_import_products_from_csv_optimized($file_path);
                } else {
                    $result = tema_souz_import_products_from_csv($file_path);
                }
                
                if ($result['success']) {
                    $message = "Импорт завершен. Обработано: {$result['processed']}, Создано: {$result['created']}, Обновлено: {$result['updated']}, Ошибок: {$result['errors']}";
                    if (isset($result['attributes_created']) && $result['attributes_created'] > 0) {
                        $message .= ", Создано атрибутов: {$result['attributes_created']}";
                    }
                    if (isset($result['attributes_assigned']) && $result['attributes_assigned'] > 0) {
                        $message .= ", Назначено атрибутов: {$result['attributes_assigned']}";
                    }
                    
                    // Показываем информацию об атрибутах
                    if ($result['attributes_created'] > 0 || $result['attributes_assigned'] > 0) {
                        $message .= "<br>Атрибуты: создано {$result['attributes_created']}, назначено {$result['attributes_assigned']}";
                    }
                    
                    // Показываем детали ошибок
                    if (!empty($result['error_details'])) {
                        $message .= '<br><strong>Детали ошибок:</strong><br>';
                        $message .= '<ul>';
                        foreach (array_slice($result['error_details'], 0, 10) as $error_detail) {
                            $message .= '<li>' . esc_html($error_detail) . '</li>';
                        }
                        if (count($result['error_details']) > 10) {
                            $message .= '<li>... и ещё ' . (count($result['error_details']) - 10) . ' ошибок</li>';
                        }
                        $message .= '</ul>';
                    }
                } else {
                    $error = "Ошибка импорта: " . $result['error'];
                }
                unlink($file_path);
            } else {
                $error = 'Ошибка загрузки файла';
            }
        } else {
            $error = 'Файл не выбран или ошибка загрузки';
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Импорт товаров WooCommerce</h1>';
    
    if ($message) {
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
    }
    if ($error) {
        echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
    }

    echo '<form method="post" enctype="multipart/form-data">';
    wp_nonce_field('custom_import_action');
    echo '<table class="form-table">';
    echo '<tr><th>CSV файл(ы)</th><td><input type="file" name="csv_files[]" accept=".csv" multiple required /></td></tr>';
    echo '<tr><th>Разделитель</th><td><select name="delimiter"><option value=",">Запятая (,)</option><option value=";">Точка с запятой (;)</option><option value="\t">Табуляция</option></select></td></tr>';
    echo '<tr><th>Кодировка</th><td><select name="encoding"><option value="utf-8">UTF-8</option><option value="windows-1251">Windows-1251</option></select></td></tr>';
    echo '<tr><th>Автоимпорт частей</th><td><label><input type="checkbox" name="auto_import_parts" value="1" /> Автоматически импортировать все части после разбиения</label><br><small>Если отмечено, после разбиения файла все части будут автоматически импортированы</small></td></tr>';
    echo '</table>';
    echo '<p class="submit">';
    echo '<input type="submit" name="import_products" class="button-primary" value="Импортировать товары" />';
    echo '<input type="submit" name="split_csv" class="button-secondary" value="Разбить большой файл на части" style="margin-left: 10px;" />';
    echo '</p>';
    echo '</form>';

    echo '<h2>Формат CSV файла</h2>';
    echo '<p><strong>Поддерживается стандартный формат WooCommerce с русскими заголовками:</strong></p>';
    echo '<p>Обязательные колонки: <code>Полное_имя</code> (или <code>name</code>), <code>Артикул</code> (или <code>sku</code>)</p>';
    echo '<p>Основные колонки: <code>Описание</code>, <code>Краткое описание</code>, <code>Базовая цена</code>, <code>Акционная цена</code>, <code>Наличие</code>, <code>Запасы</code>, <code>Вес (кг)</code>, <code>Длина (см)</code>, <code>Ширина (см)</code>, <code>Высота (см)</code>, <code>Категория</code>, <code>Метки</code>, <code>Изображения</code></p>';
    
    echo '<div class="notice notice-info">';
    echo '<h3>Оптимизация для больших файлов</h3>';
    echo '<p><strong>Автоматическая оптимизация:</strong> Файлы больше 5MB автоматически обрабатываются с оптимизированным алгоритмом:</p>';
    echo '<ul>';
    echo '<li>Пакетная обработка по 25 товаров</li>';
    echo '<li>Увеличенный лимит памяти до 2GB</li>';
    echo '<li>Отключение лимита времени выполнения</li>';
    echo '<li>Автоматическая очистка памяти</li>';
    echo '<li>Оптимизированное сохранение в базу данных</li>';
    echo '</ul>';
    echo '<p><strong>Рекомендации:</strong></p>';
    echo '<ul>';
    echo '<li>Для файлов больше 20MB рекомендуется разбить их на части</li>';
    echo '<li>Убедитесь, что на сервере достаточно свободного места</li>';
    echo '<li>Импорт больших файлов может занять несколько минут</li>';
    echo '<li><strong>Множественные файлы:</strong> Можно загружать несколько файлов одновременно</li>';
    echo '</ul>';
    echo '</div>';
    echo '<p>Атрибуты: <code>Название атрибута X</code>, <code>Значение атрибута X</code>, <code>Видимость атрибута X</code>, <code>Глобальный атрибут X</code> (где X - любой номер: 1, 2, 3, 4, 5, 6, 7...)</p>';
    echo '<p><strong>Дополнительные поля:</strong> <code>ГОСТ_прокат</code>, <code>ГОСТ_сталь</code>, <code>Внешний URL.1</code>, <code>Фото товара</code></p>';
    echo '<p><strong>Примеры из ваших файлов:</strong></p>';
    echo '<ul>';
    echo '<li><code>Полное_имя,Артикул,Базовая цена,Название атрибута 1,Значение атрибута 1,Название атрибута 2,Значение атрибута 2</code></li>';
    echo '<li><code>Полное_имя,Описание,Фото товара,Название атрибута 6,Значение атрибута 6,Название атрибута 1,Значение атрибута 1</code></li>';
    echo '</ul>';
    echo '<p><strong>Важно:</strong> Атрибуты могут идти в любом порядке (1,2,3,4,5,6 или 6,1,2,3,4,5)</p>';
    echo '</div>';
    
    // Добавляем JavaScript для отслеживания прогресса
    echo '<script>
    function checkImportProgress() {
        if (document.querySelector(".import-in-progress")) {
            fetch(ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "action=import_progress"
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const progress = data.data;
                    const progressBar = document.querySelector(".progress-bar");
                    const progressText = document.querySelector(".progress-text");
                    
                    if (progressBar) {
                        progressBar.style.width = progress.percentage + "%";
                    }
                    
                    if (progressText) {
                        progressText.innerHTML = `Часть ${progress.processed} из ${progress.total} (${progress.percentage}%) - Создано: ${progress.created}, Ошибок: ${progress.errors}`;
                    }
                    
                    if (progress.processed >= progress.total) {
                        clearInterval(progressInterval);
                        document.querySelector(".import-in-progress").style.display = "none";
                    }
                }
            })
            .catch(error => {
                console.error("Ошибка получения прогресса:", error);
            });
        }
    }
    
    // Запускаем проверку прогресса каждые 2 секунды
    let progressInterval = setInterval(checkImportProgress, 2000);
    </script>';
}

function tema_souz_import_products_from_csv($file_path) {
    $delimiter = isset($_POST['delimiter']) ? $_POST['delimiter'] : ',';
    $encoding = isset($_POST['encoding']) ? $_POST['encoding'] : 'utf-8';
    
    $result = [
        'success' => false,
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'error' => '',
        'attributes_created' => 0,
        'attributes_assigned' => 0
    ];
    
    // Кэш для созданных атрибутов в рамках этого импорта
    static $created_attributes = [];

    if (!file_exists($file_path)) {
        $result['error'] = 'Файл не найден';
        return $result;
    }

    // Увеличиваем лимиты для больших файлов
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', 0);
    set_time_limit(0);

    $handle = fopen($file_path, 'r');
    if (!$handle) {
        $result['error'] = 'Не удалось открыть файл';
        return $result;
    }

    // Читаем заголовки
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        fclose($handle);
        $result['error'] = 'Не удалось прочитать заголовки';
        return $result;
    }

    // Конвертируем кодировку если нужно
    if ($encoding !== 'utf-8') {
        $headers = array_map(function($header) use ($encoding) {
            return mb_convert_encoding($header, 'utf-8', $encoding);
        }, $headers);
    }

    // Маппинг русских заголовков на английские
    $header_mapping = [
        'ID' => 'id',
        'Тип' => 'type',
        'Артикул' => 'sku',
        'GTIN, UPC, EAN или ISBN' => 'gtin',
        'Опубликован' => 'published',
        'Рекомеднуемый' => 'featured',
        'Видимость в каталоге' => 'catalog_visibility',
        'Краткое описание' => 'short_description',
        'Дата начала действия скидки' => 'sale_date_from',
        'Дата окончания действия скидки' => 'sale_date_to',
        'Статус налога' => 'tax_status',
        'Налоговый класс' => 'tax_class',
        'Наличие' => 'stock_status',
        'Запасы' => 'stock_quantity',
        'Величина малых запасов' => 'low_stock_amount',
        'Возможен ли предзаказ?' => 'backorders',
        'Продано индивидуально?' => 'sold_individually',
        'Вес (кг)' => 'weight',
        'Длина (см)' => 'length',
        'Ширина (см)' => 'width',
        'Высота (см)' => 'height',
        'Разрешить отзывы от клиентов?' => 'reviews_allowed',
        'Примечание к покупке' => 'purchase_note',
        'Акционная цена' => 'sale_price',
        'Базовая цена' => 'regular_price',
        'Метки' => 'tags',
        'Класс доставки' => 'shipping_class',
        'Изображения' => 'images',
        'Лимит скачивания' => 'download_limit',
        'Дней срока скачивания' => 'download_expiry',
        'Родительский Сгруппированные товары' => 'parent',
        'Апсэлы' => 'upsells',
        'Кросселы' => 'cross_sells',
        'Внешний URL' => 'external_url',
        'Внешний URL.1' => 'external_url_1',
        'Текст кнопки' => 'button_text',
        'Категория' => 'categories',
        'Категории' => 'categories',
        'Полное_имя' => 'name',
        'Полное имя' => 'name',
        'Описание' => 'description',
        'Фото товара' => 'featured_image',
        'ГОСТ_прокат' => 'gost_prokat',
        'ГОСТ_сталь' => 'gost_stal'
    ];

    // Нормализуем заголовки
    $normalized_headers = [];
    foreach ($headers as $header) {
        $header = trim($header);
        if (isset($header_mapping[$header])) {
            $normalized_headers[] = $header_mapping[$header];
        } else {
            $normalized_headers[] = $header;
        }
    }

    $line_number = 1;
    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        $line_number++;
        
        // Конвертируем кодировку если нужно
        if ($encoding !== 'utf-8') {
            $data = array_map(function($field) use ($encoding) {
                return mb_convert_encoding($field, 'utf-8', $encoding);
            }, $data);
        }

        $result['processed']++;

        try {
            $product_data = array_combine($normalized_headers, $data);
            
            // Пропускаем пустые строки
            if (empty(array_filter($data))) {
                continue;
            }
            
            // Проверяем обязательные поля
            if (empty($product_data['name']) && empty($product_data['sku'])) {
                $result['errors']++;
                if (!isset($result['error_details'])) {
                    $result['error_details'] = [];
                }
                $result['error_details'][] = "Строка $line_number: Отсутствует название и SKU";
                continue;
            }

            // Игнорируем SKU - всегда создаём новые товары
            $product = new WC_Product_Simple();
            $is_update = false;

            // Заполняем основные поля
            $product->set_name($product_data['name']);
            $product->set_sku($product_data['sku']);
            
            if (!empty($product_data['description'])) {
                $product->set_description($product_data['description']);
            }
            if (!empty($product_data['short_description'])) {
                $product->set_short_description($product_data['short_description']);
            }
            if (!empty($product_data['price'])) {
                $product->set_price($product_data['price']);
            }
            if (!empty($product_data['regular_price'])) {
                $product->set_regular_price($product_data['regular_price']);
            }
            if (!empty($product_data['sale_price'])) {
                $product->set_sale_price($product_data['sale_price']);
            }
            if (!empty($product_data['weight'])) {
                $product->set_weight($product_data['weight']);
            }
            if (!empty($product_data['length'])) {
                $product->set_length($product_data['length']);
            }
            if (!empty($product_data['width'])) {
                $product->set_width($product_data['width']);
            }
            if (!empty($product_data['height'])) {
                $product->set_height($product_data['height']);
            }
            
            // Дополнительные поля из некоторых выгрузок
            if (!empty($product_data['gost_prokat'])) {
                $product->update_meta_data('_gost_prokat', $product_data['gost_prokat']);
            }
            if (!empty($product_data['gost_stal'])) {
                $product->update_meta_data('_gost_stal', $product_data['gost_stal']);
            }

            // Управление запасами
            if (isset($product_data['manage_stock'])) {
                $product->set_manage_stock($product_data['manage_stock'] === 'yes' || $product_data['manage_stock'] === '1');
            }
            if (!empty($product_data['stock_quantity'])) {
                $product->set_stock_quantity($product_data['stock_quantity']);
            }
            if (!empty($product_data['stock_status'])) {
                $product->set_stock_status($product_data['stock_status']);
            }

            // Сохраняем товар
            $product_id = $product->save();
            
            if ($is_update) {
                $result['updated']++;
            } else {
                $result['created']++;
            }

            // Обрабатываем категории
            if (!empty($product_data['categories'])) {
                $categories = explode(',', $product_data['categories']);
                $category_ids = [];
                foreach ($categories as $category_name) {
                    $category_name = trim($category_name);
                    $term = get_term_by('name', $category_name, 'product_cat');
                    if (!$term) {
                        $term = wp_insert_term($category_name, 'product_cat');
                        if (!is_wp_error($term)) {
                            $category_ids[] = $term['term_id'];
                        }
                    } else {
                        $category_ids[] = $term->term_id;
                    }
                }
                if (!empty($category_ids)) {
                    wp_set_object_terms($product_id, $category_ids, 'product_cat');
                }
            }

            // Обрабатываем теги
            if (!empty($product_data['tags'])) {
                $tags = explode(',', $product_data['tags']);
                $tag_ids = [];
                foreach ($tags as $tag_name) {
                    $tag_name = trim($tag_name);
                    $term = get_term_by('name', $tag_name, 'product_tag');
                    if (!$term) {
                        $term = wp_insert_term($tag_name, 'product_tag');
                        if (!is_wp_error($term)) {
                            $tag_ids[] = $term['term_id'];
                        }
                    } else {
                        $tag_ids[] = $term->term_id;
                    }
                }
                if (!empty($tag_ids)) {
                    wp_set_object_terms($product_id, $tag_ids, 'product_tag');
                }
            }

            // Обрабатываем атрибуты в формате WooCommerce
            $attributes_assigned = 0;
            
            // Собираем все атрибуты из данных (независимо от порядка)
            $attributes_data = [];
            foreach ($product_data as $key => $value) {
                if (preg_match('/^Название атрибута (\d+)$/', $key, $matches)) {
                    $attr_index = $matches[1];
                    if (!empty($value)) {
                        $attributes_data[$attr_index] = [
                            'name' => trim($value),
                            'value' => isset($product_data["Значение атрибута $attr_index"]) ? trim($product_data["Значение атрибута $attr_index"]) : '',
                            'visible' => isset($product_data["Видимость атрибута $attr_index"]) ? $product_data["Видимость атрибута $attr_index"] : '1',
                            'global' => isset($product_data["Глобальный атрибут $attr_index"]) ? $product_data["Глобальный атрибут $attr_index"] : '1'
                        ];
                    }
                }
            }
            
            // Обрабатываем найденные атрибуты
            foreach ($attributes_data as $attr_index => $attr_data) {
                $attr_name = $attr_data['name'];
                $attr_value = $attr_data['value'];
                $attr_visible = $attr_data['visible'];
                $attr_global = $attr_data['global'];
                
                // Пропускаем атрибуты с пустыми названиями или значениями
                if (empty($attr_name) || empty($attr_value) || $attr_name === '' || $attr_value === '') {
                    continue;
                }
                
                // Дополнительная проверка: пропускаем если значение содержит только пробелы или специальные символы
                if (trim($attr_value) === '' || preg_match('/^[\s\-_\.]+$/', $attr_value)) {
                    continue;
                }
                
                // Создаем слаг атрибута
                $attr_slug = sanitize_title($attr_name);
                $attr_slug = 'pa_' . $attr_slug;
                
                // Проверяем кэш созданных атрибутов
                if (isset($created_attributes[$attr_name])) {
                    $attr_slug = $created_attributes[$attr_name];
                    error_log("Используем кэшированный атрибут: $attr_name -> $attr_slug");
                } else {
                    // Проверяем, существует ли уже такой атрибут в БД
                    global $wpdb;
                    $existing_attribute = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($existing_attribute) {
                        // Атрибут существует, используем его
                        $attr_slug = 'pa_' . $existing_attribute->attribute_name;
                        $created_attributes[$attr_name] = $attr_slug;
                        error_log("Найден существующий атрибут: $attr_name -> $attr_slug (ID: {$existing_attribute->attribute_id})");
                    } else {
                        // Создаем новый атрибут
                        $attribute_id = wc_create_attribute([
                            'name' => $attr_name,
                            'slug' => str_replace('pa_', '', $attr_slug),
                            'type' => 'select',
                            'order_by' => 'menu_order',
                            'has_archives' => false,
                        ]);
                        
                        if (!is_wp_error($attribute_id)) {
                            register_taxonomy($attr_slug, 'product');
                            $created_attributes[$attr_name] = $attr_slug;
                            $result['attributes_created']++;
                            error_log("Создан новый атрибут: $attr_name -> $attr_slug (ID: $attribute_id)");
                        } else {
                            error_log("Ошибка создания атрибута '$attr_name': " . $attribute_id->get_error_message());
                            continue;
                        }
                    }
                }
                
                // Убеждаемся, что таксономия зарегистрирована
                if (!taxonomy_exists($attr_slug)) {
                    register_taxonomy($attr_slug, 'product');
                }
                
                // Добавляем значения атрибута
                $term_ids = [];
                $attr_values = explode(',', $attr_value);
                foreach ($attr_values as $attr_val) {
                    $attr_val = trim($attr_val);
                    if (!empty($attr_val) && $attr_val !== '') {
                        // Дополнительная проверка на пустые значения
                        if (preg_match('/^[\s\-_\.]+$/', $attr_val)) {
                            continue;
                        }
                        
                        // Ищем терм по имени
                        $term = get_term_by('name', $attr_val, $attr_slug);
                        if (!$term) {
                            // Создаём новый терм
                            $term = wp_insert_term($attr_val, $attr_slug);
                            if (!is_wp_error($term)) {
                                $term_ids[] = $term['term_id'];
                                error_log("Создан терм '$attr_val' для атрибута $attr_slug");
                            } else {
                                error_log("Ошибка создания терма '$attr_val' для атрибута $attr_slug: " . $term->get_error_message());
                            }
                        } else {
                            $term_ids[] = $term->term_id;
                            error_log("Найден существующий терм '$attr_val' (ID: {$term->term_id}) для атрибута $attr_slug");
                        }
                    }
                }
                
                if (!empty($term_ids)) {
                    // Получаем ID атрибута из БД
                    global $wpdb;
                    $attribute_data = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($attribute_data) {
                        // Создаем атрибут через WooCommerce API
                        $wc_attribute = new WC_Product_Attribute();
                        $wc_attribute->set_id($attribute_data->attribute_id);
                        $wc_attribute->set_name($attr_slug);
                        $wc_attribute->set_options($term_ids);
                        $wc_attribute->set_position(0);
                        $wc_attribute->set_visible(true);
                        $wc_attribute->set_variation(false);
                        
                        // Получаем существующие атрибуты товара
                        $existing_attributes = $product->get_attributes();
                        $existing_attributes[$attr_slug] = $wc_attribute;
                        
                        // Устанавливаем атрибуты товару
                        $product->set_attributes($existing_attributes);
                        $product->save();
                        
                        // Также назначаем термы через wp_set_object_terms для совместимости
                        $result_terms = wp_set_object_terms($product_id, $term_ids, $attr_slug);
                        
                        $attributes_assigned++;
                        $result['attributes_assigned']++;
                        error_log("Успешно назначены термы " . implode(',', $term_ids) . " атрибута $attr_slug товару $product_id через WooCommerce API (ID: {$attribute_data->attribute_id})");
                        
                        // Принудительно обновляем кэш
                        wp_cache_delete($product_id, 'posts');
                        clean_post_cache($product_id);
                        
                        // Проверяем, что атрибуты действительно назначены
                        $assigned_terms = wp_get_object_terms($product_id, $attr_slug);
                        if (!is_wp_error($assigned_terms) && !empty($assigned_terms)) {
                            error_log("Подтверждено: товар $product_id имеет " . count($assigned_terms) . " термов атрибута $attr_slug");
                        } else {
                            error_log("ПРЕДУПРЕЖДЕНИЕ: атрибут $attr_slug не назначен товару $product_id после wp_set_object_terms");
                        }
                    } else {
                        error_log("Ошибка: не найден атрибут '$attr_name' в БД для назначения товару $product_id");
                    }
                } else {
                    error_log("Нет термов для назначения атрибуту $attr_slug товару $product_id");
                }
            }
            
            // Логируем количество назначенных атрибутов
            if ($attributes_assigned > 0) {
                error_log("Товар ID $product_id: назначено $attributes_assigned атрибутов");
            } else {
                error_log("Товар ID $product_id: атрибуты не назначены");
            }
            
            // Финальная проверка и принудительное обновление атрибутов
            $final_product = wc_get_product($product_id);
            if ($final_product) {
                $final_attributes = $final_product->get_attributes();
                error_log("ФИНАЛЬНАЯ ПРОВЕРКА: товар $product_id имеет " . count($final_attributes) . " атрибутов в объекте WooCommerce");
                
                // Если атрибуты не назначены, попробуем принудительно их обновить
                if (empty($final_attributes) && $attributes_assigned > 0) {
                    error_log("ПРИНУДИТЕЛЬНОЕ ОБНОВЛЕНИЕ: атрибуты не найдены, пытаемся восстановить...");
                    
                    $force_attributes = [];
                    
                    // Используем уже собранные данные атрибутов
                    foreach ($attributes_data as $attr_index => $attr_data) {
                        $attr_name = $attr_data['name'];
                        $attr_value = $attr_data['value'];
                        
                        if (!empty($attr_name) && !empty($attr_value) && !preg_match('/^[\s\-_\.]+$/', $attr_value)) {
                            $attr_slug = 'pa_' . sanitize_title($attr_name);
                            
                            // Получаем ID атрибута
                            global $wpdb;
                            $attribute_data = $wpdb->get_row($wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                                $attr_name
                            ));
                            
                            if ($attribute_data) {
                                // Получаем термы
                                $term_ids = [];
                                $attr_values = explode(',', $attr_value);
                                foreach ($attr_values as $attr_val) {
                                    $attr_val = trim($attr_val);
                                    if (!empty($attr_val)) {
                                        $term = get_term_by('name', $attr_val, $attr_slug);
                                        if ($term) {
                                            $term_ids[] = $term->term_id;
                                        }
                                    }
                                }
                                
                                if (!empty($term_ids)) {
                                    $wc_attribute = new WC_Product_Attribute();
                                    $wc_attribute->set_id($attribute_data->attribute_id);
                                    $wc_attribute->set_name($attr_slug);
                                    $wc_attribute->set_options($term_ids);
                                    $wc_attribute->set_position(0);
                                    $wc_attribute->set_visible(true);
                                    $wc_attribute->set_variation(false);
                                    
                                    $force_attributes[$attr_slug] = $wc_attribute;
                                    error_log("Принудительно создан атрибут $attr_slug с " . count($term_ids) . " значениями");
                                }
                            }
                        }
                    }
                    
                    if (!empty($force_attributes)) {
                        $final_product->set_attributes($force_attributes);
                        $final_product->save();
                        error_log("Принудительно обновлены атрибуты для товара $product_id");
                    }
                }
                
                // Выводим названия атрибутов для отладки
                $updated_attributes = $final_product->get_attributes();
                foreach ($updated_attributes as $attr_key => $attr_obj) {
                    if (is_object($attr_obj)) {
                        $terms = $attr_obj->get_options();
                        error_log("  - Атрибут $attr_key: " . count($terms) . " значений (" . implode(', ', $terms) . ")");
                    }
                }
            }
            
            // Дополнительная проверка: убеждаемся, что атрибуты назначены
            $product_obj = wc_get_product($product_id);
            if ($product_obj) {
                $product_attributes = $product_obj->get_attributes();
                error_log("Товар ID $product_id: найдено " . count($product_attributes) . " атрибутов в объекте товара");
            }

            // Обрабатываем изображения
            $image_urls = [];
            
            // Изображения из поля "Изображения"
            if (!empty($product_data['images'])) {
                $image_urls = array_merge($image_urls, explode(',', $product_data['images']));
            }
            
            // Изображения из поля "Фото товара"
            if (!empty($product_data['featured_image'])) {
                $image_urls = array_merge($image_urls, explode(',', $product_data['featured_image']));
            }
            
            if (!empty($image_urls)) {
                $image_ids = [];
                foreach ($image_urls as $image_url) {
                    $image_url = trim($image_url);
                    if (!empty($image_url)) {
                        $attachment_id = tema_souz_import_image($image_url, $product_id);
                        if ($attachment_id) {
                            $image_ids[] = $attachment_id;
                        }
                    }
                }
                if (!empty($image_ids)) {
                    $product->set_image_id($image_ids[0]);
                    if (count($image_ids) > 1) {
                        $product->set_gallery_image_ids(array_slice($image_ids, 1));
                    }
                    $product->save();
                }
            }

        } catch (Exception $e) {
            $result['errors']++;
            $error_msg = "Ошибка импорта товара на строке $line_number: " . $e->getMessage();
            error_log($error_msg);
            
            // Сохраняем детали ошибки для отображения
            if (!isset($result['error_details'])) {
                $result['error_details'] = [];
            }
            $result['error_details'][] = "Строка $line_number: " . $e->getMessage();
        }
    }

    fclose($handle);
    $result['success'] = true;
    return $result;
}

// Оптимизированная функция импорта для больших CSV файлов
function tema_souz_import_products_from_csv_optimized($file_path) {
    $delimiter = isset($_POST['delimiter']) ? $_POST['delimiter'] : ',';
    $encoding = isset($_POST['encoding']) ? $_POST['encoding'] : 'utf-8';
    
    $result = [
        'success' => false,
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'error' => '',
        'attributes_created' => 0,
        'attributes_assigned' => 0
    ];
    
    // Кэш для созданных атрибутов в рамках этого импорта
    static $created_attributes = [];

    if (!file_exists($file_path)) {
        $result['error'] = 'Файл не найден';
        return $result;
    }

    // Увеличиваем лимиты для больших файлов
    ini_set('memory_limit', '4096M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    set_time_limit(0);
    
    // Отключаем автоматическое сохранение для ускорения
    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);
    
    // Отключаем буферизацию для предотвращения таймаутов
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Увеличиваем лимиты для загрузки файлов
    ini_set('upload_max_filesize', '500M');
    ini_set('post_max_size', '500M');
    ini_set('max_file_uploads', '20');

    $handle = fopen($file_path, 'r');
    if (!$handle) {
        $result['error'] = 'Не удалось открыть файл';
        return $result;
    }

    // Читаем заголовки
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        fclose($handle);
        $result['error'] = 'Не удалось прочитать заголовки';
        return $result;
    }

    // Конвертируем кодировку если нужно
    if ($encoding !== 'utf-8') {
        $headers = array_map(function($header) use ($encoding) {
            return mb_convert_encoding($header, 'utf-8', $encoding);
        }, $headers);
    }

    // Маппинг русских заголовков на английские
    $header_mapping = [
        'ID' => 'id',
        'Тип' => 'type',
        'Артикул' => 'sku',
        'GTIN, UPC, EAN или ISBN' => 'gtin',
        'Опубликован' => 'published',
        'Полное_имя' => 'name',
        'name' => 'name',
        'Описание' => 'description',
        'Краткое описание' => 'short_description',
        'Базовая цена' => 'price',
        'Акционная цена' => 'sale_price',
        'Наличие' => 'stock_status',
        'Запасы' => 'stock_quantity',
        'Вес (кг)' => 'weight',
        'Длина (см)' => 'length',
        'Ширина (см)' => 'width',
        'Высота (см)' => 'height',
        'Категория' => 'categories',
        'Метки' => 'tags',
        'Изображения' => 'images',
        'Управление запасами' => 'manage_stock',
        'Статус запасов' => 'stock_status',
        'Количество запасов' => 'stock_quantity',
        'Тип проката' => 'type_prokat',
        'ГОСТ проката' => 'gost_prokat',
        'ГОСТ стали' => 'gost_stal',
        'Сталь' => 'steel',
        'Название атрибута 1' => 'Название атрибута 1',
        'Значения атрибутов 1' => 'Значение атрибута 1',
        'Название атрибута 2' => 'Название атрибута 2',
        'Значения атрибутов 2' => 'Значение атрибута 2',
        'Название атрибута 3' => 'Название атрибута 3',
        'Значения атрибутов 3' => 'Значение атрибута 3',
        'Название атрибута 4' => 'Название атрибута 4',
        'Значения атрибутов 4' => 'Значение атрибута 4',
        'Название атрибута 5' => 'Название атрибута 5',
        'Значения атрибутов 5' => 'Значение атрибута 5',
        'Название атрибута 6' => 'Название атрибута 6',
        'Значения атрибутов 6' => 'Значение атрибута 6',
    ];

    $normalized_headers = [];
    foreach ($headers as $header) {
        $header = trim($header);
        if (isset($header_mapping[$header])) {
            $normalized_headers[] = $header_mapping[$header];
        } else {
            $normalized_headers[] = $header;
        }
    }

    $line_number = 1;
    $batch_size = 10; // Уменьшаем размер пакета для предотвращения таймаутов
    $batch_count = 0;
    $products_batch = [];
    $last_flush_time = time();
    
    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        $line_number++;
        
        // Конвертируем кодировку если нужно
        if ($encoding !== 'utf-8') {
            $data = array_map(function($field) use ($encoding) {
                return mb_convert_encoding($field, 'utf-8', $encoding);
            }, $data);
        }

        $result['processed']++;
        $batch_count++;

        try {
            $product_data = array_combine($normalized_headers, $data);
            
            // Пропускаем пустые строки
            if (empty(array_filter($data))) {
                continue;
            }
            
            // Проверяем обязательные поля
            if (empty($product_data['name']) && empty($product_data['sku'])) {
                $result['errors']++;
                continue;
            }

            // Добавляем товар в пакет
            $products_batch[] = $product_data;
            
            // Обрабатываем пакет когда он заполнен или прошло время
            $current_time = time();
            if (count($products_batch) >= $batch_size || ($current_time - $last_flush_time) > 30) {
                $batch_result = tema_souz_process_products_batch($products_batch, $line_number - $batch_size, $created_attributes);
                $result['created'] += $batch_result['created'];
                $result['updated'] += $batch_result['updated'];
                $result['errors'] += $batch_result['errors'];
                $result['attributes_created'] += $batch_result['attributes_created'];
                $result['attributes_assigned'] += $batch_result['attributes_assigned'];
                
                // Агрессивная очистка памяти
                $products_batch = [];
                wp_cache_flush();
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                // Принудительная очистка памяти
                if (function_exists('memory_get_usage') && memory_get_usage() > 1024 * 1024 * 1024) { // Больше 1GB
                    wp_cache_flush();
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }
                
                $last_flush_time = $current_time;
                
                // Сохраняем состояние импорта
                tema_souz_save_import_state($file_path, $line_number, $result);
                
                // Небольшая пауза для предотвращения перегрузки
                usleep(50000); // 0.05 секунды
            }
            
        } catch (Exception $e) {
            $result['errors']++;
            error_log("Ошибка импорта товара на строке $line_number: " . $e->getMessage());
        }
    }
    
    // Обрабатываем оставшиеся товары
    if (!empty($products_batch)) {
        $batch_result = tema_souz_process_products_batch($products_batch, $line_number - count($products_batch), $created_attributes);
        $result['created'] += $batch_result['created'];
        $result['updated'] += $batch_result['updated'];
        $result['errors'] += $batch_result['errors'];
        $result['attributes_created'] += $batch_result['attributes_created'];
        $result['attributes_assigned'] += $batch_result['attributes_assigned'];
    }

    fclose($handle);
    
    // Включаем обратно автоматическое сохранение
    wp_defer_term_counting(false);
    wp_defer_comment_counting(false);
    
    $result['success'] = true;
    return $result;
}

// Функция для обработки пакета товаров
function tema_souz_process_products_batch($products_batch, $start_line, &$created_attributes) {
    $result = [
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'attributes_created' => 0,
        'attributes_assigned' => 0
    ];
    
    foreach ($products_batch as $index => $product_data) {
        $line_number = $start_line + $index + 1;
        
        try {
            // Игнорируем SKU - всегда создаём новые товары
            $product = new WC_Product_Simple();
            $is_update = false;

            // Заполняем основные поля
            $product->set_name($product_data['name']);
            $product->set_sku($product_data['sku']);
            
            if (!empty($product_data['description'])) {
                $product->set_description($product_data['description']);
            }
            if (!empty($product_data['short_description'])) {
                $product->set_short_description($product_data['short_description']);
            }
            if (!empty($product_data['price'])) {
                $product->set_price($product_data['price']);
            }
            if (!empty($product_data['regular_price'])) {
                $product->set_regular_price($product_data['regular_price']);
            }
            if (!empty($product_data['sale_price'])) {
                $product->set_sale_price($product_data['sale_price']);
            }
            if (!empty($product_data['weight'])) {
                $product->set_weight($product_data['weight']);
            }
            if (!empty($product_data['length'])) {
                $product->set_length($product_data['length']);
            }
            if (!empty($product_data['width'])) {
                $product->set_width($product_data['width']);
            }
            if (!empty($product_data['height'])) {
                $product->set_height($product_data['height']);
            }
            
            // Дополнительные поля
            if (!empty($product_data['gost_prokat'])) {
                $product->update_meta_data('_gost_prokat', $product_data['gost_prokat']);
            }
            if (!empty($product_data['gost_stal'])) {
                $product->update_meta_data('_gost_stal', $product_data['gost_stal']);
            }

            // Управление запасами
            if (isset($product_data['manage_stock'])) {
                $product->set_manage_stock($product_data['manage_stock'] === 'yes' || $product_data['manage_stock'] === '1');
            }
            if (!empty($product_data['stock_quantity'])) {
                $product->set_stock_quantity($product_data['stock_quantity']);
            }
            if (!empty($product_data['stock_status'])) {
                $product->set_stock_status($product_data['stock_status']);
            }

            // Сохраняем товар
            $product_id = $product->save();
            
            if ($is_update) {
                $result['updated']++;
            } else {
                $result['created']++;
            }

            // Обрабатываем категории
            if (!empty($product_data['categories'])) {
                $categories = explode(',', $product_data['categories']);
                $category_ids = [];
                foreach ($categories as $category_name) {
                    $category_name = trim($category_name);
                    $term = get_term_by('name', $category_name, 'product_cat');
                    if ($term) {
                        $category_ids[] = $term->term_id;
                    } else {
                        $new_term = wp_insert_term($category_name, 'product_cat');
                        if (!is_wp_error($new_term)) {
                            $category_ids[] = $new_term['term_id'];
                        }
                    }
                }
                if (!empty($category_ids)) {
                    wp_set_object_terms($product_id, $category_ids, 'product_cat');
                }
            }

            // Обрабатываем теги
            if (!empty($product_data['tags'])) {
                $tags = explode(',', $product_data['tags']);
                $tag_ids = [];
                foreach ($tags as $tag_name) {
                    $tag_name = trim($tag_name);
                    $term = get_term_by('name', $tag_name, 'product_tag');
                    if ($term) {
                        $tag_ids[] = $term->term_id;
                    } else {
                        $new_term = wp_insert_term($tag_name, 'product_tag');
                        if (!is_wp_error($new_term)) {
                            $tag_ids[] = $new_term['term_id'];
                        }
                    }
                }
                if (!empty($tag_ids)) {
                    wp_set_object_terms($product_id, $tag_ids, 'product_tag');
                }
            }

            // Обрабатываем изображения
            if (!empty($product_data['images'])) {
                $image_urls = explode(',', $product_data['images']);
                $image_ids = [];
                foreach ($image_urls as $image_url) {
                    $image_url = trim($image_url);
                    if (!empty($image_url)) {
                        $attachment_id = tema_souz_import_image($image_url, $product_id);
                        if ($attachment_id) {
                            $image_ids[] = $attachment_id;
                        }
                    }
                }
                if (!empty($image_ids)) {
                    $product->set_gallery_image_ids($image_ids);
                    if (!empty($image_ids[0])) {
                        $product->set_image_id($image_ids[0]);
                    }
                    $product->save();
                }
            }

            // Обрабатываем атрибуты
            $attributes_assigned = 0;
            $attribute_index = 1;
            
            while (isset($product_data["Название атрибута $attribute_index"])) {
                $attr_name = trim($product_data["Название атрибута $attribute_index"]);
                $attr_value = trim($product_data["Значение атрибута $attribute_index"]);
                
                // Пропускаем атрибуты с пустыми названиями или значениями
                if (empty($attr_name) || empty($attr_value) || $attr_name === '' || $attr_value === '') {
                    $attribute_index++;
                    continue;
                }
                
                // Дополнительная проверка: пропускаем если значение содержит только пробелы или специальные символы
                if (trim($attr_value) === '' || preg_match('/^[\s\-_\.]+$/', $attr_value)) {
                    $attribute_index++;
                    continue;
                }
                
                // Создаем слаг атрибута
                $attr_slug = sanitize_title($attr_name);
                $attr_slug = 'pa_' . $attr_slug;
                
                // Проверяем кэш созданных атрибутов
                if (isset($created_attributes[$attr_name])) {
                    $attr_slug = $created_attributes[$attr_name];
                } else {
                    // Проверяем, существует ли уже такой атрибут в БД
                    global $wpdb;
                    $existing_attribute = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($existing_attribute) {
                        // Атрибут существует, используем его
                        $attr_slug = 'pa_' . $existing_attribute->attribute_name;
                        $created_attributes[$attr_name] = $attr_slug;
                    } else {
                        // Создаем новый атрибут
                        $attribute_id = wc_create_attribute([
                            'name' => $attr_name,
                            'slug' => str_replace('pa_', '', $attr_slug),
                            'type' => 'select',
                            'order_by' => 'menu_order',
                            'has_archives' => false,
                        ]);
                        
                        if ($attribute_id) {
                            $created_attributes[$attr_name] = $attr_slug;
                            $result['attributes_created']++;
                            
                            // Регистрируем таксономию
                            register_taxonomy($attr_slug, 'product');
                        }
                    }
                }
                
                // Добавляем атрибут к товару
                $attribute = new WC_Product_Attribute();
                $attribute->set_name($attr_slug);
                $attribute->set_options([$attr_value]);
                $attribute->set_position(0);
                $attribute->set_visible(true);
                $attribute->set_variation(false);
                
                $product->set_attributes([$attribute]);
                $product->save();
                
                // Создаем термин для атрибута
                $term = wp_insert_term($attr_value, $attr_slug);
                if (!is_wp_error($term)) {
                    wp_set_object_terms($product_id, $term['term_id'], $attr_slug);
                    $attributes_assigned++;
                }
                
                $attribute_index++;
            }
            
            $result['attributes_assigned'] += $attributes_assigned;
            
        } catch (Exception $e) {
            $result['errors']++;
            error_log("Ошибка обработки товара на строке $line_number: " . $e->getMessage());
        }
    }
    
    return $result;
}

// Функция для разбиения больших CSV файлов на части
function tema_souz_split_large_csv($file_path, $max_rows_per_file = 1000) {
    $result = [
        'success' => false,
        'files' => [],
        'error' => ''
    ];
    
    if (!file_exists($file_path)) {
        $result['error'] = 'Файл не найден';
        return $result;
    }
    
    $delimiter = isset($_POST['delimiter']) ? $_POST['delimiter'] : ',';
    $encoding = isset($_POST['encoding']) ? $_POST['encoding'] : 'utf-8';
    
    $handle = fopen($file_path, 'r');
    if (!$handle) {
        $result['error'] = 'Не удалось открыть файл';
        return $result;
    }
    
    // Читаем заголовки
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        fclose($handle);
        $result['error'] = 'Не удалось прочитать заголовки';
        return $result;
    }
    
    // Конвертируем кодировку если нужно
    if ($encoding !== 'utf-8') {
        $headers = array_map(function($header) use ($encoding) {
            return mb_convert_encoding($header, 'utf-8', $encoding);
        }, $headers);
    }
    
    $file_info = pathinfo($file_path);
    $output_dir = $file_info['dirname'];
    $base_name = $file_info['filename'];
    $extension = $file_info['extension'];
    
    $file_count = 1;
    $current_row = 0;
    $current_file = null;
    $current_handle = null;
    
    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        // Конвертируем кодировку если нужно
        if ($encoding !== 'utf-8') {
            $data = array_map(function($field) use ($encoding) {
                return mb_convert_encoding($field, 'utf-8', $encoding);
            }, $data);
        }
        
        // Создаем новый файл если нужно
        if ($current_row === 0) {
            if ($current_handle) {
                fclose($current_handle);
            }
            
            $new_filename = $base_name . '_part_' . $file_count . '.' . $extension;
            $new_filepath = $output_dir . '/' . $new_filename;
            
            $current_handle = fopen($new_filepath, 'w');
            if (!$current_handle) {
                $result['error'] = "Не удалось создать файл: $new_filename";
                fclose($handle);
                return $result;
            }
            
            // Записываем заголовки в новый файл
            fputcsv($current_handle, $headers, $delimiter);
            
            $result['files'][] = $new_filepath;
            $file_count++;
        }
        
        // Записываем строку данных
        fputcsv($current_handle, $data, $delimiter);
        $current_row++;
        
        // Если достигли лимита строк, начинаем новый файл
        if ($current_row >= $max_rows_per_file) {
            $current_row = 0;
        }
    }
    
    if ($current_handle) {
        fclose($current_handle);
    }
    
    fclose($handle);
    $result['success'] = true;
    
    return $result;
}

// Функция для мониторинга процесса импорта
function tema_souz_import_progress_monitor() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Недостаточно прав.');
    }
    
    $upload_dir = wp_upload_dir();
    $progress_file = $upload_dir['path'] . '/import_progress.json';
    
    if (file_exists($progress_file)) {
        $progress = json_decode(file_get_contents($progress_file), true);
        wp_send_json_success($progress);
    } else {
        wp_send_json_error('Файл прогресса не найден');
    }
}

// AJAX обработчик для мониторинга прогресса
add_action('wp_ajax_import_progress', 'tema_souz_import_progress_monitor');

// Функция для записи прогресса импорта
function tema_souz_write_import_progress($processed, $total, $created, $updated, $errors) {
    $upload_dir = wp_upload_dir();
    $progress_file = $upload_dir['path'] . '/import_progress.json';
    
    $progress = [
        'processed' => $processed,
        'total' => $total,
        'created' => $created,
        'updated' => $updated,
        'errors' => $errors,
        'percentage' => $total > 0 ? round(($processed / $total) * 100, 2) : 0,
        'timestamp' => current_time('mysql'),
        'memory_usage' => function_exists('memory_get_usage') ? memory_get_usage(true) : 0,
        'peak_memory' => function_exists('memory_get_peak_usage') ? memory_get_peak_usage(true) : 0
    ];
    
    file_put_contents($progress_file, json_encode($progress));
}

// Функция для сохранения состояния импорта
function tema_souz_save_import_state($file_path, $line_number, $result) {
    $upload_dir = wp_upload_dir();
    $state_file = $upload_dir['path'] . '/import_state.json';
    
    $state = [
        'file_path' => $file_path,
        'line_number' => $line_number,
        'result' => $result,
        'timestamp' => current_time('mysql'),
        'memory_usage' => function_exists('memory_get_usage') ? memory_get_usage(true) : 0
    ];
    
    file_put_contents($state_file, json_encode($state));
}

// Функция для восстановления состояния импорта
function tema_souz_restore_import_state() {
    $upload_dir = wp_upload_dir();
    $state_file = $upload_dir['path'] . '/import_state.json';
    
    if (file_exists($state_file)) {
        $state = json_decode(file_get_contents($state_file), true);
        if ($state && isset($state['file_path']) && file_exists($state['file_path'])) {
            return $state;
        }
    }
    
    return null;
}

// Функция для очистки файла прогресса
function tema_souz_clear_import_progress() {
    $upload_dir = wp_upload_dir();
    $progress_file = $upload_dir['path'] . '/import_progress.json';
    
    if (file_exists($progress_file)) {
        unlink($progress_file);
    }
}

function tema_souz_import_image($image_url, $product_id) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    
    if ($image_data === false) {
        return false;
    }
    
    $filename = basename($image_url);
    $file_path = $upload_dir['path'] . '/' . $filename;
    
    if (file_put_contents($file_path, $image_data) === false) {
        return false;
    }
    
    $attachment = [
        'post_mime_type' => wp_check_filetype($filename)['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit',
        'post_parent' => $product_id,
    ];
    
    $attachment_id = wp_insert_attachment($attachment, $file_path);
    
    if (!is_wp_error($attachment_id)) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        return $attachment_id;
    }
    
    return false;
}

function tema_souz_clear_catalog_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Недостаточно прав.');
    }

    $message = '';
    $error = '';

    if (isset($_POST['clear_catalog']) && check_admin_referer('clear_catalog_action')) {
        $result = tema_souz_clear_woocommerce_catalog();
        if ($result['success']) {
            $message = "Очистка завершена. Удалено: товаров {$result['products_deleted']}, вариаций {$result['variations_deleted']}, категорий {$result['categories_deleted']}, атрибутов {$result['attributes_deleted']}";
        } else {
            $error = "Ошибка очистки: " . $result['error'];
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Очистка каталога WooCommerce</h1>';
    
    if ($message) {
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
    }
    if ($error) {
        echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
    }

    echo '<div class="notice notice-warning">';
    echo '<p><strong>ВНИМАНИЕ!</strong> Это действие безвозвратно удалит:</p>';
    echo '<ul>';
    echo '<li>Все товары и их вариации</li>';
    echo '<li>Все категории товаров</li>';
    echo '<li>Все атрибуты товаров</li>';
    echo '<li>Все теги товаров</li>';
    echo '<li>Все изображения товаров</li>';
    echo '</ul>';
    echo '<p>Это действие нельзя отменить!</p>';
    echo '</div>';

    echo '<form method="post" onsubmit="return confirm(\'Вы уверены, что хотите полностью очистить каталог? Это действие нельзя отменить!\');">';
    wp_nonce_field('clear_catalog_action');
    echo '<p><input type="checkbox" name="confirm_clear" value="1" required /> Я понимаю, что это действие нельзя отменить</p>';
    echo '<p class="submit"><input type="submit" name="clear_catalog" class="button button-primary" value="ОЧИСТИТЬ КАТАЛОГ" style="background-color: #dc3232; border-color: #dc3232;" /></p>';
    echo '</form>';
    echo '</div>';
}

function tema_souz_clear_woocommerce_catalog() {
    $result = [
        'success' => false,
        'products_deleted' => 0,
        'variations_deleted' => 0,
        'categories_deleted' => 0,
        'attributes_deleted' => 0,
        'error' => ''
    ];

    if (!isset($_POST['confirm_clear']) || $_POST['confirm_clear'] !== '1') {
        $result['error'] = 'Подтверждение не получено';
        return $result;
    }

    try {
        // 1. Удаляем все вариации товаров
        $variations_query = new WP_Query([
            'post_type' => 'product_variation',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        if ($variations_query->have_posts()) {
            foreach ($variations_query->posts as $variation_id) {
                wp_delete_post($variation_id, true);
                $result['variations_deleted']++;
            }
            wp_reset_postdata();
        }

        // 2. Удаляем все товары
        $products_query = new WP_Query([
            'post_type' => 'product',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        if ($products_query->have_posts()) {
            foreach ($products_query->posts as $product_id) {
                // Очищаем кэш товара
                if (function_exists('wc_delete_product_transients')) {
                    wc_delete_product_transients($product_id);
                }
                wp_delete_post($product_id, true);
                $result['products_deleted']++;
            }
            wp_reset_postdata();
        }

        // 3. Удаляем все категории товаров
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category_id) {
                // Удаляем категорию принудительно
                $deleted = wp_delete_term($category_id, 'product_cat');
                if (!is_wp_error($deleted) && $deleted !== false) {
                    $result['categories_deleted']++;
                }
            }
        }
        
        // 3.1. Дополнительная очистка категорий через БД
        global $wpdb;
        
        // Удаляем все связи категорий с товарами
        $wpdb->query("DELETE tr FROM {$wpdb->term_relationships} tr 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                      WHERE tt.taxonomy = 'product_cat'");
        
        // Удаляем все записи таксономии категорий
        $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'product_cat'");
        
        // Удаляем все термы категорий
        $wpdb->query("DELETE t FROM {$wpdb->terms} t 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
                      WHERE tt.taxonomy = 'product_cat'");
        
        // Удаляем мета-данные категорий
        $wpdb->query("DELETE tm FROM {$wpdb->termmeta} tm 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id 
                      WHERE tt.taxonomy = 'product_cat'");

        // 4. Удаляем все теги товаров
        $tags = get_terms([
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
            'fields' => 'ids',
        ]);

        if (!is_wp_error($tags) && !empty($tags)) {
            foreach ($tags as $tag_id) {
                wp_delete_term($tag_id, 'product_tag');
            }
        }
        
        // 4.1. Дополнительная очистка тегов через БД
        // Удаляем все связи тегов с товарами
        $wpdb->query("DELETE tr FROM {$wpdb->term_relationships} tr 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                      WHERE tt.taxonomy = 'product_tag'");
        
        // Удаляем все записи таксономии тегов
        $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'product_tag'");
        
        // Удаляем все термы тегов
        $wpdb->query("DELETE t FROM {$wpdb->terms} t 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
                      WHERE tt.taxonomy = 'product_tag'");
        
        // Удаляем мета-данные тегов
        $wpdb->query("DELETE tm FROM {$wpdb->termmeta} tm 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id 
                      WHERE tt.taxonomy = 'product_tag'");

        // 5. Удаляем все атрибуты товаров
        
        // Получаем все атрибуты
        $attributes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");
        
        foreach ($attributes as $attribute) {
            $taxonomy_name = 'pa_' . $attribute->attribute_name;
            
            // Удаляем все термы атрибута
            $terms = get_terms([
                'taxonomy' => $taxonomy_name,
                'hide_empty' => false,
                'fields' => 'ids',
            ]);
            
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term_id) {
                    wp_delete_term($term_id, $taxonomy_name);
                }
            }
            
            // Удаляем атрибут через WooCommerce API
            if (function_exists('wc_delete_attribute')) {
                wc_delete_attribute($attribute->attribute_id);
            } else {
                // Альтернативный способ - прямое удаление из БД
                $wpdb->delete(
                    $wpdb->prefix . 'woocommerce_attribute_taxonomies',
                    ['attribute_id' => $attribute->attribute_id],
                    ['%d']
                );
            }
            
            // Удаляем саму таксономию
            unregister_taxonomy($taxonomy_name);
            
            $result['attributes_deleted']++;
        }
        
        // 6. Дополнительная очистка: удаляем все записи из таблицы term_taxonomy для атрибутов
        $wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy LIKE 'pa_%'");
        
        // 7. Очищаем таблицу term_relationships от связей с атрибутами
        $wpdb->query("DELETE tr FROM {$wpdb->term_relationships} tr 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                      WHERE tt.taxonomy LIKE 'pa_%'");
        
        // 8. Очищаем таблицу terms от термов атрибутов
        $wpdb->query("DELETE t FROM {$wpdb->terms} t 
                      INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
                      WHERE tt.taxonomy LIKE 'pa_%'");

        // 9. Очищаем кэш
        wp_cache_flush();
        
        // 10. Очищаем кэш WooCommerce
        if (function_exists('wc_delete_product_transients')) {
            // Очищаем все кэши товаров
        }

        $result['success'] = true;
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }

    return $result;
}

// Инструмент в админке: Инструменты → Включить атрибуты для вариаций
add_action('admin_menu', function () {
	add_management_page(
		'Включить атрибуты для вариаций',
		'Включить атрибуты для вариаций',
		'manage_woocommerce',
		'mark-attrs-variation',
		function () {
			if (!current_user_can('manage_woocommerce')) {
				wp_die('Недостаточно прав.');
			}

			$target = array('pa_diameter','pa_thickness','pa_steel','pa_material'); // добавьте свои слаги при необходимости
			$updated = 0;
			$ran = false;

			if (isset($_POST['mark_attrs_variation']) && check_admin_referer('mark_attrs_variation_action')) {
				$ran = true;
				$args = array(
					'status' => array('publish','draft','pending','private'),
					'limit'  => -1,
					'return' => 'objects',
					'type'   => array('variable','simple'),
				);
				$products = wc_get_products($args);

				foreach ($products as $product) {
					$pid = $product->get_id();
					$changed = false;

					// 1) Прямая правка сериализованной меты _product_attributes
					$raw = get_post_meta($pid, '_product_attributes', true);
					if (is_array($raw)) {
						foreach ($raw as $k => $row) {
							$key = is_string($k) ? $k : (isset($row['name']) ? $row['name'] : '');
							if (!$key) continue;
							if (in_array($key, $target, true)) {
								if (empty($row['is_variation'])) { $row['is_variation'] = 1; $changed = true; }
								$raw[$k] = $row;
							}
						}
						if ($changed) {
							update_post_meta($pid, '_product_attributes', $raw);
						}
					}

					// 2) Через объектную модель WC (для согласованности кэшей)
					$attrs = $product->get_attributes(); // массив WC_Product_Attribute
					foreach ($attrs as $a_key => $attr) {
						$name = $attr->get_name();
						if (in_array($name, $target, true) && !$attr->get_variation()) {
							$attr->set_variation(true);
							$attrs[$a_key] = $attr;
							$changed = true;
						}
					}
					if ($changed) {
						$product->set_attributes($attrs);
					}

					// Дополнительно: для всех вариаций установить статус "В наличии" и базовую цену 0.
					// Если у вариативного товара нет ни одной вариации — создадим базовую вариацию с "любыми" значениями.
					if ($product->is_type('variable')) {
						$children = $product->get_children();
						if (empty($children)) {
							// Создаём пустую вариацию
							$variation_id = wp_insert_post([
								'post_title'   => $product->get_name() . ' – базовая вариация',
								'post_name'    => 'variation-' . $product->get_id() . '-' . wp_generate_password(8, false),
								'post_status'  => 'publish',
								'post_parent'  => $product->get_id(),
								'post_type'    => 'product_variation',
								'menu_order'   => 0,
							]);
							if (!is_wp_error($variation_id) && $variation_id) {
								// Проставим пустые атрибуты (любой) для всех атрибутов, используемых для вариаций
								$prod_attrs = $product->get_attributes();
								foreach ($prod_attrs as $pa) {
									if (!$pa->get_variation()) continue;
									$name = $pa->get_name(); // pa_*
									update_post_meta($variation_id, 'attribute_' . $name, ''); // пусто = любой
								}
								$children = [$variation_id];
							}
						}

						foreach ($children as $vid) {
							$var = wc_get_product($vid);
							if (!$var) { continue; }
							// Снять управление запасами и принудительно включить наличие
							if (method_exists($var, 'set_manage_stock')) { $var->set_manage_stock(false); }
							$var->set_stock_status('instock');
							if (method_exists($var, 'set_backorders')) { $var->set_backorders('no'); }
							$var->set_regular_price('0');
							$var->set_sale_price('');
							$var->set_price('0');
							$var->save();
						}
						// Обновим статус родительского товара
						$product->set_stock_status('instock');
					}

					// Сохраняем товар, если были изменения в атрибутах или обновляли вариации
					$product->save();
					// Очистим кэши, чтобы статус применился сразу
					if (function_exists('wc_delete_product_transients')) { wc_delete_product_transients($product->get_id()); }
					$updated++;
				}
			}

			echo '<div class="wrap"><h1>Включить атрибуты для вариаций</h1>';
			if ($ran) {
				echo '<div class="notice notice-success"><p>Готово. Обновлено товаров: <strong>' . intval($updated) . '</strong></p></div>';
			}
			echo '<form method="post">';
			wp_nonce_field('mark_attrs_variation_action');
			submit_button('Запустить обработку', 'primary', 'mark_attrs_variation');
			echo '</form></div>';
		}
	);

	// Инструмент: Инструменты → Очистить каталог (удалить все товары и вариации)
	add_management_page(
		'Очистить каталог (удалить все товары)',
		'Очистить каталог',
		'manage_woocommerce',
		'wc-purge-products',
		function () {
			if (!current_user_can('manage_woocommerce')) {
				wp_die('Недостаточно прав.');
			}
			$deleted = 0;
			$ran = false;
			if (isset($_POST['wc_purge_products']) && check_admin_referer('wc_purge_products_action')) {
				$ran = true;
				// 1) Удаляем вариации товаров
				$var_q = new WP_Query([
					'post_type'      => 'product_variation',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				]);
				if ($var_q->have_posts()) {
					foreach ($var_q->posts as $vid) {
						wp_delete_post($vid, true);
						$deleted++;
					}
					wp_reset_postdata();
				}
				// 2) Удаляем товары
				$prod_q = new WP_Query([
					'post_type'      => 'product',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				]);
				if ($prod_q->have_posts()) {
					foreach ($prod_q->posts as $pid) {
						if (function_exists('wc_delete_product_transients')) { wc_delete_product_transients($pid); }
						wp_delete_post($pid, true);
						$deleted++;
					}
					wp_reset_postdata();
				}
			}

			echo '<div class="wrap"><h1>Очистить каталог (удалить все товары)</h1>';
			if ($ran) {
				echo '<div class="notice notice-success"><p>Удалено записей: <strong>' . intval($deleted) . '</strong></p></div>';
			}
			echo '<form method="post" onsubmit="return confirm(\'Действительно удалить ВСЕ товары и вариации без возможности восстановления?\');">';
			wp_nonce_field('wc_purge_products_action');
			echo '<p>Это действие безвозвратно удалит все товары и вариации из каталога.</p>';
			submit_button('Удалить все товары', 'delete', 'wc_purge_products');
			echo '</form></div>';
		}
	);

	// Инструмент: Инструменты → Назначить дефолтные значения вариаций всем товарам
	add_management_page(
		'Назначить дефолтные вариации',
		'Дефолтные вариации',
		'manage_woocommerce',
		'wc-assign-default-variations',
		function () {
			if (!current_user_can('manage_woocommerce')) {
				wp_die('Недостаточно прав.');
			}
			$updated = 0; $processed = 0; $skipped = 0;
			$ran = false;
			if (isset($_POST['wc_assign_defaults']) && check_admin_referer('wc_assign_defaults_action')) {
				$ran = true;
				$products = wc_get_products([
					'type'   => ['variable'],
					'limit'  => -1,
					'status' => ['publish','draft','private','pending'],
					'return' => 'objects',
				]);
				foreach ($products as $product) {
					$processed++;
					// Гарантируем, что атрибуты помечены как вариативные
					$attrs = $product->get_attributes();
					$attr_changed = false;
					foreach ($attrs as $akey => $aobj) {
						if (method_exists($aobj, 'get_variation') && !$aobj->get_variation()) {
							$aobj->set_variation(true);
							$attrs[$akey] = $aobj;
							$attr_changed = true;
						}
					}
					if ($attr_changed) { $product->set_attributes($attrs); }
					// Формируем дефолты строго для трёх атрибутов: диаметр, толщина, сталь
					$target = ['pa_diameter','pa_thickness','pa_steel','pa_material'];
					$defaults = [];
					foreach ($target as $tax) {
						if (!taxonomy_exists($tax)) continue;
						// Сначала берём первый назначенный терм у товара
						$slugs = wp_get_post_terms($product->get_id(), $tax, ['fields' => 'slugs']);
						if (!is_wp_error($slugs) && !empty($slugs)) {
							$defaults[$tax] = (string) $slugs[0];
							continue;
						}
						// Иначе пробуем взять из первой вариации, где значение не пусто
						$found = '';
						foreach ($product->get_children() as $vid) {
							$va = get_post_meta($vid, 'attribute_' . $tax, true);
							if ($va !== '' && $va !== null) { $found = (string) $va; break; }
						}
						if ($found !== '') { $defaults[$tax] = $found; }
					}
					if (empty($defaults)) { $skipped++; continue; }
					$product->set_default_attributes($defaults);
					$product->save();
					if (function_exists('wc_delete_product_transients')) { wc_delete_product_transients($product->get_id()); }
					$updated++;
				}
			}

			echo '<div class="wrap"><h1>Назначить дефолтные вариации</h1>';
			if ($ran) {
				echo '<div class="notice notice-success"><p>Обработано: <strong>' . intval($processed) . '</strong>, назначено: <strong>' . intval($updated) . '</strong>, пропущено: <strong>' . intval($skipped) . '</strong></p></div>';
			}
			echo '<form method="post">';
			wp_nonce_field('wc_assign_defaults_action');
			echo '<p>Скрипт пройдётся по всем вариативным товарам и выставит значения по умолчанию по первой доступной вариации (либо по первой, если доступных нет).</p>';
			submit_button('Назначить дефолтные вариации', 'primary', 'wc_assign_defaults');
			echo '</form></div>';
		}
	);
});
add_action('edited_product_cat', function ($term_id) {
    if (isset($_POST['product_cat_hero_image'])) {
        update_term_meta($term_id, 'product_cat_hero_image', (int) $_POST['product_cat_hero_image']);
    }
});

add_action('admin_enqueue_scripts', function () {
    if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== 'product_cat') return;
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', <<<'JS'
jQuery(function($){
    var frame;
    function updateBtn(has){
        $('#product_cat_hero_remove').toggle(!!has);
        $('#product_cat_hero_upload').text(has ? 'Заменить изображение' : 'Выбрать изображение');
    }
    $('#product_cat_hero_upload').on('click', function(e){
        e.preventDefault();
        if (frame) { frame.open(); return; }
        frame = wp.media({ title: 'Выберите HERO изображение', button: { text: 'Использовать' }, multiple: false });
        frame.on('select', function(){
            var at = frame.state().get('selection').first().toJSON();
            $('#product_cat_hero_image').val(at.id);
            $('#product_cat_hero_preview').html('<img src="'+at.url+'" style="max-width:200px;height:auto;" />');
            updateBtn(true);
        });
        frame.open();
    });
    $('#product_cat_hero_remove').on('click', function(){
        $('#product_cat_hero_image').val('');
        $('#product_cat_hero_preview').empty();
        updateBtn(false);
    });
});
JS
    );
});

// Подключение шаблонных частей header/footer через wp_head/wp_footer
// Вывод разметки поиска, мобильного меню и т.п. будет в header.php/footer.php согласно исходному HTML

// Поддержка Contact Form 7: отключить авто p
add_filter('wpcf7_autop_or_not', '__return_false');

// Регистрация sidebar (если понадобится)
add_action('widgets_init', function () {
	register_sidebar([
        'name'          => __('Сайдбар', 'tema-souz'),
		'id'            => 'sidebar-1',
        'before_widget' => '<section class="widget">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	]);
});

// Авто-сокращение базового слага атрибута (для глобальных атрибутов Woo) до 28 символов
if (!function_exists('tema_souz_shorten_attribute_base_slug')) {
    function tema_souz_shorten_attribute_base_slug($label) {
        $raw = is_string($label) ? $label : '';
        if ($raw === '') return '';
        
        // Сначала пробуем транслитерацию
        $ascii = remove_accents($raw);
        $slug  = sanitize_title($ascii);
        
        // Если транслитерация дала пустой результат, используем оригинал
        if ($slug === '') {
            $slug = sanitize_title($raw);
        }
        
        // Если всё ещё пустой, используем дефолт
        if ($slug === '') {
            $slug = 'attr';
        }
        
        // База для wc_attribute_taxonomy_name() должна быть <= 28 символов
        // WooCommerce может считать по-разному, поэтому делаем более агрессивное сокращение
        if (strlen($slug) > 28) {
            // Убираем дефисы и сокращаем до 25 символов (запас)
            $slug = str_replace('-', '', $slug);
            if (strlen($slug) > 25) {
                $slug = substr($slug, 0, 25);
            }
        }
        
        return $slug;
    }
}

// WooCommerce CSV импорт: если имя атрибута длиннее — автоматически сокращаем базовый слаг до 28 символов
add_filter('woocommerce_product_importer_parsed_data', function ($parsed_data, $importer) {
    if (!is_array($parsed_data)) return $parsed_data;
    if (empty($parsed_data['attributes']) || !is_array($parsed_data['attributes'])) return $parsed_data;

    foreach ($parsed_data['attributes'] as $idx => $attr) {
        // Структура атрибута может приходить разной: проверим name/slug
        $name = isset($attr['name']) ? (string) $attr['name'] : '';
        $taxonomy = isset($attr['taxonomy']) ? (string) $attr['taxonomy'] : '';

        // Если уже указан явный taxonomy вида pa_xxx — проверим только базу
        if ($taxonomy !== '' && strpos($taxonomy, 'pa_') === 0) {
            $base = substr($taxonomy, 3);
            if (mb_strlen($base) > 28) {
                $short = tema_souz_shorten_attribute_base_slug($base);
                $parsed_data['attributes'][$idx]['taxonomy'] = 'pa_' . $short;
            }
            continue;
        }

        // Иначе у нас есть метка (name). Построим короткий базовый слаг
        $label_for_slug = $name !== '' ? $name : (isset($attr['label']) ? (string) $attr['label'] : '');
        if ($label_for_slug !== '') {
            $short = tema_souz_shorten_attribute_base_slug($label_for_slug);
            // Сохраним в taxonomy: pa_{short}. Woo сам создаст глобальный атрибут при необходимости
            $parsed_data['attributes'][$idx]['taxonomy'] = 'pa_' . $short;
            // А человекочитаемую метку оставим как есть
        }
    }
    return $parsed_data;
}, 10, 2);

// Глобально: укоротить базу слага для Woo атрибутов (перехватывает все места, где Woo генерирует pa_*)
add_filter('woocommerce_attribute_taxonomy_name', function ($attribute_name) {
    // В Woo сюда может приходить уже с префиксом pa_
    $base = (string) preg_replace('~^pa_~', '', (string) $attribute_name);
    $short = tema_souz_shorten_attribute_base_slug($base);
    return 'pa_' . $short;
}, 0);

// Дополнительная защита: принудительное сокращение при создании атрибутов
add_filter('woocommerce_attribute_taxonomy_name', function ($attribute_name) {
    $base = (string) preg_replace('~^pa_~', '', (string) $attribute_name);
    
    // Принудительно сокращаем до 20 символов (запас)
    if (strlen($base) > 20) {
        $base = substr($base, 0, 20);
        // Убираем дефис в конце, если он есть
        $base = rtrim($base, '-');
    }
    
    return 'pa_' . $base;
}, 1);

// Санитизация базы таксономии атрибута Woo: гарантируем длину ≤ 28 и ASCII-форму
add_filter('woocommerce_sanitize_taxonomy_name', function ($sanitized, $raw) {
    $raw = is_string($raw) ? $raw : '';
    // Преобразуем в ASCII для таксономий (Woo требует ASCII для базы)
    $ascii = remove_accents($raw);
    $slug  = sanitize_title($ascii);
    if ($slug === '') { $slug = 'attr'; }
    
    // Принудительно сокращаем до 15 символов (большой запас)
    if (strlen($slug) > 15) {
        $slug = substr($slug, 0, 15);
        $slug = rtrim($slug, '-');
    }
    
    return $slug;
}, 9, 2);

// Отключение проверки длины слага атрибутов WooCommerce
add_filter('woocommerce_rest_prepare_product_attribute', function ($response, $object, $request) {
    if (isset($response->data['slug'])) {
        $slug = $response->data['slug'];
        if (strlen($slug) > 15) {
            $response->data['slug'] = substr($slug, 0, 15);
            $response->data['slug'] = rtrim($response->data['slug'], '-');
        }
    }
    return $response;
}, 10, 3);

// Отключение валидации длины слага в WooCommerce
add_filter('woocommerce_rest_product_attribute_schema', function ($schema) {
    if (isset($schema['properties']['slug']['maxLength'])) {
        unset($schema['properties']['slug']['maxLength']);
    }
    return $schema;
});

// Отключение проверки длины слага при создании атрибутов
add_filter('woocommerce_rest_product_attribute_object_trashable', function ($trashable, $object) {
    return $trashable;
}, 10, 2);

// Перехват валидации атрибутов и отключение проверки длины
add_action('woocommerce_rest_insert_product_attribute', function ($attribute, $request, $creating) {
    // Принудительно разрешаем любой слаг
    return true;
}, 10, 3);

// Отключение проверки длины слага в админке
add_filter('woocommerce_rest_prepare_product_attribute', function ($response, $object, $request) {
    // Убираем ограничения на длину слага
    if (isset($response->data['slug'])) {
        // Оставляем слаг как есть, без ограничений
    }
    return $response;
}, 5, 3);

// Радикальное решение: перехват создания атрибутов на уровне WordPress
add_action('created_product_attribute', function ($attribute_id, $args) {
    $slug = $args['attribute_name'] ?? '';
    if (strlen($slug) > 28) {
        // Принудительно обновляем слаг на короткий
        $short_slug = substr($slug, 0, 28);
        $short_slug = rtrim($short_slug, '-');
        
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'woocommerce_attribute_taxonomies',
            ['attribute_name' => $short_slug],
            ['attribute_id' => $attribute_id]
        );
    }
}, 10, 2);

// Перехват при импорте: принудительно сокращаем слаг
add_filter('woocommerce_product_importer_parsed_data', function ($parsed_data, $importer) {
    if (!is_array($parsed_data)) return $parsed_data;
    if (empty($parsed_data['attributes']) || !is_array($parsed_data['attributes'])) return $parsed_data;

    foreach ($parsed_data['attributes'] as $idx => $attr) {
        $name = isset($attr['name']) ? (string) $attr['name'] : '';
        $taxonomy = isset($attr['taxonomy']) ? (string) $attr['taxonomy'] : '';

        // Если слаг длиннее 28 символов - принудительно сокращаем
        if ($taxonomy !== '' && strpos($taxonomy, 'pa_') === 0) {
            $base = substr($taxonomy, 3);
            if (strlen($base) > 28) {
                $short = substr($base, 0, 28);
                $short = rtrim($short, '-');
                $parsed_data['attributes'][$idx]['taxonomy'] = 'pa_' . $short;
            }
        }
    }
    return $parsed_data;
}, 5, 2);

// Разрешаем кириллицу и Unicode в слагах: мягкая санитизация
add_filter('sanitize_title', function ($title, $raw_title, $context) {
    // В админке, на фронте и при импорте — всегда используем мягкую обработку: удаляем управляющие символы, но оставляем буквы/цифры/пробелы Unicode
    $src = (string) ($raw_title !== '' ? $raw_title : $title);
    // Уберём управляющие и нежелательные: оставим буквы/цифры/дефисы/подчёркивания/пробелы (Unicode)
    $clean = preg_replace('~[\p{C}\p{Zl}\p{Zp}]+~u', ' ', $src); // control + спец пробелы → обычный пробел
    $clean = preg_replace('~[\t\n\r]+~u', ' ', $clean);
    $clean = preg_replace('~\s+~u', ' ', trim((string) $clean));
    // Заменим пробелы на дефисы, сохраняя Unicode
    $clean = str_replace(' ', '-', $clean);
    // Удалим подряд идущие дефисы
    $clean = preg_replace('~-{2,}~u', '-', $clean);
    // Удалим ведущие/замыкающие дефисы/подчёркивания/точки
    $clean = preg_replace('~^[\-_.]+|[\-_.]+$~u', '', $clean);
    return $clean !== '' ? $clean : $title;
}, 9, 3);

// 2) Не трогаем уже заданный пост-слаг при вставке/обновлении (если пришёл из импорта)
add_filter('wp_unique_post_slug', function ($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
    if ($post_type !== 'product' && $post_type !== 'product_variation') return $slug;
    $is_import = defined('WP_IMPORTING') || (isset($_REQUEST['woocommerce_importer']) || isset($_REQUEST['wc_import'])) || (defined('DOING_CRON') && DOING_CRON);
    if ($is_import && is_string($original_slug) && $original_slug !== '') {
        // Сохраняем оригинальный слаг из файла импорта, доверяя уникальности, которую обеспечит WP при конфликте
        return $original_slug;
    }
    return $slug;
}, 10, 6);

// 3) Для импорта через CRUD WC — не пересоздавать слаг автоматически
add_filter('woocommerce_product_pre_insert_product_object', function ($data, $product) {
    if (!is_array($data)) return $data;
    $is_import = defined('WP_IMPORTING') || (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && strpos((string) $_REQUEST['action'], 'woocommerce_csv') !== false);
    if ($is_import) {
        if (!empty($data['post_name'])) {
            // Зафиксируем переданный slug как есть
            $data['post_name'] = (string) $data['post_name'];
        } else {
            // Если slug не задан — попробуем SKU, иначе возьмём ровно заголовок, без sanitize_title
            $candidate = '';
            if (!empty($data['sku'])) {
                $candidate = (string) $data['sku'];
            } elseif (!empty($data['post_title'])) {
                $candidate = (string) $data['post_title'];
            }
            if ($candidate !== '') {
                $data['post_name'] = $candidate;
            }
        }
    }
    return $data;
}, 10, 2);

function tema_souz_import_attributes_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Недостаточно прав.');
    }

    $message = '';
    $error = '';

    if (isset($_POST['split_attributes_csv']) && check_admin_referer('import_attributes_action')) {
        if (isset($_FILES['csv_files']) && !empty($_FILES['csv_files']['name'][0])) {
            $upload_dir = wp_upload_dir();
            $uploaded_files = [];
            
            // Обрабатываем каждый загруженный файл
            for ($i = 0; $i < count($_FILES['csv_files']['name']); $i++) {
                if ($_FILES['csv_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = sanitize_file_name($_FILES['csv_files']['name'][$i]);
                    $file_path = $upload_dir['path'] . '/' . $file_name;
                    
                    if (move_uploaded_file($_FILES['csv_files']['tmp_name'][$i], $file_path)) {
                        $uploaded_files[] = $file_path;
                    }
                }
            }
            
            if (!empty($uploaded_files)) {
                $message = "Загружено файлов: " . count($uploaded_files) . "<br>";
                
                // Проверяем, нужно ли автоматически импортировать части
                $auto_import = isset($_POST['auto_import_attributes_parts']) && $_POST['auto_import_attributes_parts'] === '1';
                
                if ($auto_import) {
                    $message .= "<br><strong>Начинаем автоматический импорт атрибутов всех файлов...</strong><br>";
                    $message .= '<div class="import-in-progress" style="margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px;">';
                    $message .= '<div style="background: #ddd; height: 20px; border-radius: 10px; overflow: hidden;">';
                    $message .= '<div class="progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
                    $message .= '</div>';
                    $message .= '<div class="progress-text" style="margin-top: 10px; font-weight: bold;">Подготовка к импорту атрибутов...</div>';
                    $message .= '</div>';
                    
                    $total_result = [
                        'processed' => 0,
                        'updated' => 0,
                        'errors' => 0,
                        'attributes_assigned' => 0
                    ];
                    
                    $file_count = 0;
                    foreach ($uploaded_files as $file_path) {
                        $file_count++;
                        $filename = basename($file_path);
                        $file_size = filesize($file_path);
                        
                        $message .= "<br>Обрабатываем файл $file_count из " . count($uploaded_files) . ": <strong>$filename</strong> (" . round($file_size/1024/1024, 2) . " MB)<br>";
                        
                        // Проверяем размер файла для выбора функции импорта
                        $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                        
                        if ($use_optimized) {
                            $result = tema_souz_import_attributes_from_csv_optimized($file_path);
                        } else {
                            $result = tema_souz_import_attributes_from_csv($file_path);
                        }
                        
                        if ($result['success']) {
                            $total_result['processed'] += $result['processed'];
                            $total_result['updated'] += $result['updated'];
                            $total_result['errors'] += $result['errors'];
                            $total_result['attributes_assigned'] += $result['attributes_assigned'];
                            
                            $message .= "✓ Обработано: {$result['processed']}, Обновлено товаров: {$result['updated']}, Назначено атрибутов: {$result['attributes_assigned']}, Ошибок: {$result['errors']}<br>";
                        } else {
                            $message .= "✗ Ошибка импорта: " . $result['error'] . "<br>";
                            $total_result['errors']++;
                        }
                        
                        // Очищаем память между файлами
                        wp_cache_flush();
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                        
                        // Удаляем временный файл
                        unlink($file_path);
                    }
                    
                    $message .= "<br><strong>Итоговый результат импорта атрибутов:</strong><br>";
                    $message .= "Обработано файлов: $file_count<br>";
                    $message .= "Всего обработано: {$total_result['processed']}<br>";
                    $message .= "Обновлено товаров: {$total_result['updated']}<br>";
                    $message .= "Назначено атрибутов: {$total_result['attributes_assigned']}<br>";
                    $message .= "Ошибок: {$total_result['errors']}<br>";
                    
                } else {
                    // Обычный импорт без автоматического разбиения
                    $total_result = [
                        'processed' => 0,
                        'updated' => 0,
                        'errors' => 0,
                        'attributes_assigned' => 0
                    ];
                    
                    foreach ($uploaded_files as $file_path) {
                        $filename = basename($file_path);
                        $file_size = filesize($file_path);
                        
                        $message .= "<br>Импортируем файл: <strong>$filename</strong> (" . round($file_size/1024/1024, 2) . " MB)<br>";
                        
                        // Проверяем размер файла для выбора функции импорта
                        $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                        
                        if ($use_optimized) {
                            $result = tema_souz_import_attributes_from_csv_optimized($file_path);
                        } else {
                            $result = tema_souz_import_attributes_from_csv($file_path);
                        }
                        
                        if ($result['success']) {
                            $total_result['processed'] += $result['processed'];
                            $total_result['updated'] += $result['updated'];
                            $total_result['errors'] += $result['errors'];
                            $total_result['attributes_assigned'] += $result['attributes_assigned'];
                            
                            $message .= "✓ Обработано: {$result['processed']}, Обновлено товаров: {$result['updated']}, Назначено атрибутов: {$result['attributes_assigned']}, Ошибок: {$result['errors']}<br>";
                        } else {
                            $message .= "✗ Ошибка импорта: " . $result['error'] . "<br>";
                            $total_result['errors']++;
                        }
                        
                        // Очищаем память между файлами
                        wp_cache_flush();
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                        
                        // Удаляем временный файл
                        unlink($file_path);
                    }
                    
                    $message .= "<br><strong>Итоговый результат импорта атрибутов:</strong><br>";
                    $message .= "Обработано файлов: " . count($uploaded_files) . "<br>";
                    $message .= "Всего обработано: {$total_result['processed']}<br>";
                    $message .= "Обновлено товаров: {$total_result['updated']}<br>";
                    $message .= "Назначено атрибутов: {$total_result['attributes_assigned']}<br>";
                    $message .= "Ошибок: {$total_result['errors']}<br>";
                }
            } else {
                $error = 'Не удалось загрузить файлы';
            }
        } else {
            $error = 'Выберите файлы для импорта';
        }
    } elseif (isset($_POST['import_attributes']) && check_admin_referer('import_attributes_action')) {
        if (isset($_FILES['csv_files']) && !empty($_FILES['csv_files']['name'][0])) {
            $upload_dir = wp_upload_dir();
            $uploaded_files = [];
            
            // Обрабатываем каждый загруженный файл
            for ($i = 0; $i < count($_FILES['csv_files']['name']); $i++) {
                if ($_FILES['csv_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_name = sanitize_file_name($_FILES['csv_files']['name'][$i]);
                    $file_path = $upload_dir['path'] . '/' . $file_name;
                    
                    if (move_uploaded_file($_FILES['csv_files']['tmp_name'][$i], $file_path)) {
                        $uploaded_files[] = $file_path;
                    }
                }
            }
            
            if (!empty($uploaded_files)) {
                $total_result = [
                    'processed' => 0,
                    'updated' => 0,
                    'errors' => 0,
                    'attributes_assigned' => 0
                ];
                
                foreach ($uploaded_files as $file_path) {
                    $filename = basename($file_path);
                    $file_size = filesize($file_path);
                    
                    $message .= "<br>Импортируем файл: <strong>$filename</strong> (" . round($file_size/1024/1024, 2) . " MB)<br>";
                    
                    // Проверяем размер файла для выбора функции импорта
                    $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                    
                    if ($use_optimized) {
                        $result = tema_souz_import_attributes_from_csv_optimized($file_path);
                    } else {
                        $result = tema_souz_import_attributes_from_csv($file_path);
                    }
                    
                    if ($result['success']) {
                        $total_result['processed'] += $result['processed'];
                        $total_result['updated'] += $result['updated'];
                        $total_result['errors'] += $result['errors'];
                        $total_result['attributes_assigned'] += $result['attributes_assigned'];
                        
                        $message .= "✓ Обработано: {$result['processed']}, Обновлено товаров: {$result['updated']}, Назначено атрибутов: {$result['attributes_assigned']}, Ошибок: {$result['errors']}<br>";
                    } else {
                        $message .= "✗ Ошибка импорта: " . $result['error'] . "<br>";
                        $total_result['errors']++;
                    }
                    
                    // Очищаем память между файлами
                    wp_cache_flush();
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                    
                    // Удаляем временный файл
                    unlink($file_path);
                }
                
                $message = "Импорт атрибутов завершен. Обработано файлов: " . count($uploaded_files) . "<br>";
                $message .= "Всего обработано: {$total_result['processed']}, Обновлено товаров: {$total_result['updated']}, Ошибок: {$total_result['errors']}";
                if (isset($total_result['attributes_assigned']) && $total_result['attributes_assigned'] > 0) {
                    $message .= ", Назначено атрибутов: {$total_result['attributes_assigned']}";
                }
            } else {
                $error = 'Не удалось загрузить файлы';
            }
        } else {
            $error = 'Выберите файлы для импорта';
        }
    } elseif (isset($_POST['split_attributes_csv']) && check_admin_referer('import_attributes_action')) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file'];
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['path'] . '/' . sanitize_file_name($file['name']);
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $split_result = tema_souz_split_large_csv($file_path, 1000);
                if ($split_result['success']) {
                    $file_count = count($split_result['files']);
                    $message = "Файл успешно разбит на $file_count частей:<br>";
                    
                    // Проверяем, нужно ли автоматически импортировать части
                    $auto_import = isset($_POST['auto_import_attributes_parts']) && $_POST['auto_import_attributes_parts'] === '1';
                    
                    if ($auto_import) {
                        $message .= "<br><strong>Начинаем автоматический импорт атрибутов всех частей...</strong><br>";
                        $message .= '<div class="import-in-progress" style="margin: 20px 0; padding: 15px; background: #f0f0f0; border-radius: 5px;">';
                        $message .= '<div style="background: #ddd; height: 20px; border-radius: 10px; overflow: hidden;">';
                        $message .= '<div class="progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>';
                        $message .= '</div>';
                        $message .= '<div class="progress-text" style="margin-top: 10px; font-weight: bold;">Подготовка к импорту атрибутов...</div>';
                        $message .= '</div>';
                        
                        $total_result = [
                            'processed' => 0,
                            'updated' => 0,
                            'errors' => 0,
                            'attributes_assigned' => 0
                        ];
                        
                        foreach ($split_result['files'] as $index => $split_file) {
                            $filename = basename($split_file);
                            $part_number = $index + 1;
                            $message .= "<br>Импортируем атрибуты части $part_number из $file_count: <strong>$filename</strong><br>";
                            
                            // Записываем прогресс
                            tema_souz_write_import_progress(
                                $part_number, 
                                $file_count, 
                                $total_result['updated'], 
                                $total_result['updated'], 
                                $total_result['errors']
                            );
                            
                            // Импортируем часть
                            $part_result = tema_souz_import_attributes_from_csv_optimized($split_file);
                            
                            if ($part_result['success']) {
                                $total_result['processed'] += $part_result['processed'];
                                $total_result['updated'] += $part_result['updated'];
                                $total_result['errors'] += $part_result['errors'];
                                $total_result['attributes_assigned'] += $part_result['attributes_assigned'];
                                
                                $message .= "✓ Обработано: {$part_result['processed']}, Обновлено товаров: {$part_result['updated']}, Назначено атрибутов: {$part_result['attributes_assigned']}, Ошибок: {$part_result['errors']}<br>";
                            } else {
                                $message .= "✗ Ошибка импорта: " . $part_result['error'] . "<br>";
                                $total_result['errors']++;
                            }
                            
                            // Очищаем память между частями
                            wp_cache_flush();
                            if (function_exists('gc_collect_cycles')) {
                                gc_collect_cycles();
                            }
                            
                            // Небольшая пауза для стабильности
                            usleep(100000); // 0.1 секунды
                        }
                        
                        $message .= "<br><strong>Итоговый результат импорта атрибутов:</strong><br>";
                        $message .= "Всего обработано: {$total_result['processed']}<br>";
                        $message .= "Обновлено товаров: {$total_result['updated']}<br>";
                        $message .= "Назначено атрибутов: {$total_result['attributes_assigned']}<br>";
                        $message .= "Ошибок: {$total_result['errors']}<br>";
                        
                        // Удаляем временные файлы частей
                        foreach ($split_result['files'] as $split_file) {
                            if (file_exists($split_file)) {
                                unlink($split_file);
                            }
                        }
                        $message .= "<br>Временные файлы частей удалены.";
                        
                        // Очищаем файл прогресса
                        tema_souz_clear_import_progress();
                        
                    } else {
                        foreach ($split_result['files'] as $split_file) {
                            $filename = basename($split_file);
                            $message .= "• <a href='" . $upload_dir['url'] . "/$filename' target='_blank'>$filename</a><br>";
                        }
                        $message .= "<br>Теперь вы можете импортировать атрибуты каждого файла отдельно.";
                    }
                } else {
                    $error = "Ошибка разбиения файла: " . $split_result['error'];
                }
            } else {
                $error = 'Не удалось загрузить файл';
            }
        } else {
            $error = 'Выберите файл для разбиения';
        }
    } elseif (isset($_POST['import_attributes']) && check_admin_referer('import_attributes_action')) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file'];
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['path'] . '/' . sanitize_file_name($file['name']);
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Проверяем размер файла для выбора функции импорта
                $file_size = filesize($file_path);
                $use_optimized = $file_size > 5 * 1024 * 1024; // Больше 5MB
                
                if ($use_optimized) {
                    $result = tema_souz_import_attributes_from_csv_optimized($file_path);
                } else {
                    $result = tema_souz_import_attributes_from_csv($file_path);
                }
                
                if ($result['success']) {
                    $message = "Импорт атрибутов завершен. Обработано: {$result['processed']}, Обновлено товаров: {$result['updated']}, Ошибок: {$result['errors']}";
                    if (isset($result['attributes_assigned']) && $result['attributes_assigned'] > 0) {
                        $message .= ", Назначено атрибутов: {$result['attributes_assigned']}";
                    }
                    if (isset($result['error_details']) && !empty($result['error_details'])) {
                        $message .= "<br><strong>Детали ошибок:</strong><br>" . implode("<br>", $result['error_details']);
                    }
                } else {
                    $error = "Ошибка импорта: " . $result['error'];
                }
                unlink($file_path);
            } else {
                $error = 'Не удалось загрузить файл';
            }
        } else {
            $error = 'Файл не выбран или ошибка загрузки';
        }
    }

    echo '<div class="wrap">';
    echo '<h1>Импорт атрибутов товаров</h1>';
    
    if ($message) {
        echo '<div class="notice notice-success"><p>' . $message . '</p></div>';
    }
    if ($error) {
        echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
    }

    echo '<div class="notice notice-info">';
    echo '<p><strong>Внимание!</strong> Этот скрипт импортирует только атрибуты к существующим товарам.</p>';
    echo '<p>Товары должны уже существовать в каталоге. Товары определяются по SKU или названию.</p>';
    echo '</div>';

    echo '<form method="post" enctype="multipart/form-data">';
    wp_nonce_field('import_attributes_action');
    echo '<table class="form-table">';
    echo '<tr><th scope="row">CSV файл(ы)</th><td><input type="file" name="csv_files[]" accept=".csv" multiple required /></td></tr>';
    echo '<tr><th scope="row">Разделитель</th><td><select name="delimiter"><option value=",">Запятая (,)</option><option value=";">Точка с запятой (;)</option><option value="\t">Табуляция</option></select></td></tr>';
    echo '<tr><th scope="row">Кодировка</th><td><select name="encoding"><option value="utf-8">UTF-8</option><option value="windows-1251">Windows-1251</option><option value="cp1251">CP1251</option></select></td></tr>';
    echo '<tr><th scope="row">Автоимпорт частей</th><td><label><input type="checkbox" name="auto_import_attributes_parts" value="1" /> Автоматически импортировать атрибуты всех частей после разбиения</label><br><small>Если отмечено, после разбиения файла все части будут автоматически импортированы</small></td></tr>';
    echo '</table>';
    echo '<p class="submit">';
    echo '<input type="submit" name="import_attributes" class="button button-primary" value="Импортировать атрибуты" />';
    echo '<input type="submit" name="split_attributes_csv" class="button button-secondary" value="Разбить большой файл на части" style="margin-left: 10px;" />';
    echo '</p>';
    echo '</form>';

    echo '<div class="notice notice-warning">';
    echo '<h3>Формат файла:</h3>';
    echo '<p>Файл должен содержать колонки:</p>';
    echo '<ul>';
    echo '<li><code>Артикул</code> или <code>Полное_имя</code> - для поиска товара</li>';
    echo '<li><code>Название атрибута 1</code>, <code>Значение атрибута 1</code></li>';
    echo '<li><code>Название атрибута 2</code>, <code>Значение атрибута 2</code></li>';
    echo '<li>И так далее для всех атрибутов...</li>';
    echo '</ul>';
    echo '<p><strong>Пример:</strong> <code>Артикул,Название атрибута 1,Значение атрибута 1,Название атрибута 2,Значение атрибута 2</code></p>';
    echo '</div>';
    
    echo '<div class="notice notice-info">';
    echo '<h3>Оптимизация для больших файлов</h3>';
    echo '<p><strong>Автоматическая оптимизация:</strong> Файлы больше 5MB автоматически обрабатываются с оптимизированным алгоритмом:</p>';
    echo '<ul>';
    echo '<li>Пакетная обработка по 25 товаров</li>';
    echo '<li>Увеличенный лимит памяти до 2GB</li>';
    echo '<li>Отключение лимита времени выполнения</li>';
    echo '<li>Автоматическая очистка памяти</li>';
    echo '<li>Оптимизированное сохранение в базу данных</li>';
    echo '</ul>';
    echo '<p><strong>Рекомендации:</strong></p>';
    echo '<ul>';
    echo '<li>Для файлов больше 20MB рекомендуется разбить их на части</li>';
    echo '<li>Убедитесь, что товары уже существуют в каталоге</li>';
    echo '<li>Импорт больших файлов может занять несколько минут</li>';
    echo '<li><strong>Множественные файлы:</strong> Можно загружать несколько файлов одновременно</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
}

function tema_souz_import_attributes_from_csv($file_path) {
    $delimiter = isset($_POST['delimiter']) ? $_POST['delimiter'] : ',';
    $encoding = isset($_POST['encoding']) ? $_POST['encoding'] : 'utf-8';
    
    $result = [
        'success' => false,
        'processed' => 0,
        'updated' => 0,
        'errors' => 0,
        'error' => '',
        'attributes_assigned' => 0
    ];
    
    // Кэш для созданных атрибутов в рамках этого импорта
    static $created_attributes = [];

    if (!file_exists($file_path)) {
        $result['error'] = 'Файл не найден';
        return $result;
    }

    $handle = fopen($file_path, 'r');
    if (!$handle) {
        $result['error'] = 'Не удалось открыть файл';
        return $result;
    }

    // Читаем заголовки
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        fclose($handle);
        $result['error'] = 'Не удалось прочитать заголовки';
        return $result;
    }

    // Конвертируем кодировку если нужно
    if ($encoding !== 'utf-8') {
        $headers = array_map(function($header) use ($encoding) {
            return mb_convert_encoding($header, 'utf-8', $encoding);
        }, $headers);
    }

    // Маппинг русских заголовков на английские
    $header_mapping = [
        'Артикул' => 'sku',
        'Полное_имя' => 'name',
        'Название' => 'name'
    ];

    // Нормализуем заголовки
    $normalized_headers = [];
    foreach ($headers as $header) {
        $header = trim($header);
        if (isset($header_mapping[$header])) {
            $normalized_headers[] = $header_mapping[$header];
        } else {
            $normalized_headers[] = $header;
        }
    }

    $line_number = 1;
    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        $line_number++;
        
        // Конвертируем кодировку если нужно
        if ($encoding !== 'utf-8') {
            $data = array_map(function($field) use ($encoding) {
                return mb_convert_encoding($field, 'utf-8', $encoding);
            }, $data);
        }

        $result['processed']++;

        try {
            $product_data = array_combine($normalized_headers, $data);
            
            // Пропускаем пустые строки
            if (empty(array_filter($data))) {
                continue;
            }
            
            // Ищем товар по SKU или названию
            $product = null;
            if (!empty($product_data['sku'])) {
                $product_id = wc_get_product_id_by_sku($product_data['sku']);
                if ($product_id) {
                    $product = wc_get_product($product_id);
                }
            }
            
            // Если не найден по SKU, ищем по названию
            if (!$product && !empty($product_data['name'])) {
                $products = wc_get_products([
                    'name' => $product_data['name'],
                    'limit' => 1,
                    'return' => 'ids'
                ]);
                if (!empty($products)) {
                    $product = wc_get_product($products[0]);
                }
            }
            
            if (!$product) {
                $result['errors']++;
                if (!isset($result['error_details'])) {
                    $result['error_details'] = [];
                }
                $result['error_details'][] = "Строка $line_number: Товар не найден (SKU: {$product_data['sku']}, Название: {$product_data['name']})";
                continue;
            }
            
            $product_id = $product->get_id();
            $attributes_assigned = 0;
            $attribute_index = 1;
            
            // Обрабатываем атрибуты
            while (isset($product_data["Название атрибута $attribute_index"])) {
                $attr_name = trim($product_data["Название атрибута $attribute_index"]);
                $attr_value = trim($product_data["Значение атрибута $attribute_index"]);
                
                // Пропускаем атрибуты с пустыми названиями или значениями
                if (empty($attr_name) || empty($attr_value) || $attr_name === '' || $attr_value === '') {
                    $attribute_index++;
                    continue;
                }
                
                // Дополнительная проверка: пропускаем если значение содержит только пробелы или специальные символы
                if (trim($attr_value) === '' || preg_match('/^[\s\-_\.]+$/', $attr_value)) {
                    $attribute_index++;
                    continue;
                }
                
                // Создаем слаг атрибута
                $attr_slug = sanitize_title($attr_name);
                $attr_slug = 'pa_' . $attr_slug;
                
                // Проверяем кэш созданных атрибутов
                if (isset($created_attributes[$attr_name])) {
                    $attr_slug = $created_attributes[$attr_name];
                    error_log("Используем кэшированный атрибут: $attr_name -> $attr_slug");
                } else {
                    // Проверяем, существует ли уже такой атрибут в БД
                    global $wpdb;
                    $existing_attribute = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($existing_attribute) {
                        // Атрибут существует, используем его
                        $attr_slug = 'pa_' . $existing_attribute->attribute_name;
                        $created_attributes[$attr_name] = $attr_slug;
                        error_log("Найден существующий атрибут: $attr_name -> $attr_slug (ID: {$existing_attribute->attribute_id})");
                    } else {
                        // Создаем новый атрибут
                        $attribute_id = wc_create_attribute([
                            'name' => $attr_name,
                            'slug' => str_replace('pa_', '', $attr_slug),
                            'type' => 'select',
                            'order_by' => 'menu_order',
                            'has_archives' => false,
                        ]);
                        
                        if (!is_wp_error($attribute_id)) {
                            register_taxonomy($attr_slug, 'product');
                            $created_attributes[$attr_name] = $attr_slug;
                            error_log("Создан новый атрибут: $attr_name -> $attr_slug (ID: $attribute_id)");
                        } else {
                            error_log("Ошибка создания атрибута '$attr_name': " . $attribute_id->get_error_message());
                            $attribute_index++;
                            continue;
                        }
                    }
                }
                
                // Убеждаемся, что таксономия зарегистрирована
                if (!taxonomy_exists($attr_slug)) {
                    register_taxonomy($attr_slug, 'product');
                }
                
                // Добавляем значения атрибута
                $term_ids = [];
                $attr_values = explode(',', $attr_value);
                foreach ($attr_values as $attr_val) {
                    $attr_val = trim($attr_val);
                    if (!empty($attr_val) && $attr_val !== '') {
                        // Дополнительная проверка на пустые значения
                        if (preg_match('/^[\s\-_\.]+$/', $attr_val)) {
                            continue;
                        }
                        
                        // Ищем терм по имени
                        $term = get_term_by('name', $attr_val, $attr_slug);
                        if (!$term) {
                            // Создаём новый терм
                            $term = wp_insert_term($attr_val, $attr_slug);
                            if (!is_wp_error($term)) {
                                $term_ids[] = $term['term_id'];
                                error_log("Создан терм '$attr_val' для атрибута $attr_slug");
                            } else {
                                error_log("Ошибка создания терма '$attr_val' для атрибута $attr_slug: " . $term->get_error_message());
                            }
                        } else {
                            $term_ids[] = $term->term_id;
                            error_log("Найден существующий терм '$attr_val' (ID: {$term->term_id}) для атрибута $attr_slug");
                        }
                    }
                }
                
                if (!empty($term_ids)) {
                    // Получаем ID атрибута из БД
                    global $wpdb;
                    $attribute_data = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($attribute_data) {
                        // Создаем атрибут через WooCommerce API
                        $wc_attribute = new WC_Product_Attribute();
                        $wc_attribute->set_id($attribute_data->attribute_id);
                        $wc_attribute->set_name($attr_slug);
                        $wc_attribute->set_options($term_ids);
                        $wc_attribute->set_position(0);
                        $wc_attribute->set_visible(true);
                        $wc_attribute->set_variation(false);
                        
                        // Получаем существующие атрибуты товара
                        $existing_attributes = $product->get_attributes();
                        $existing_attributes[$attr_slug] = $wc_attribute;
                        
                        // Устанавливаем атрибуты товару
                        $product->set_attributes($existing_attributes);
                        $product->save();
                        
                        // Также назначаем термы через wp_set_object_terms для совместимости
                        wp_set_object_terms($product_id, $term_ids, $attr_slug);
                        
                        $attributes_assigned++;
                        $result['attributes_assigned']++;
                        error_log("Успешно назначены термы " . implode(',', $term_ids) . " атрибута $attr_slug товару $product_id через WooCommerce API (ID: {$attribute_data->attribute_id})");
                        
                        // Принудительно обновляем кэш
                        wp_cache_delete($product_id, 'posts');
                        clean_post_cache($product_id);
                    } else {
                        error_log("Ошибка: не найден атрибут '$attr_name' в БД для назначения товару $product_id");
                    }
                } else {
                    error_log("Нет термов для назначения атрибуту $attr_slug товару $product_id");
                }
                
                $attribute_index++;
            }
            
            if ($attributes_assigned > 0) {
                $result['updated']++;
                error_log("Товар ID $product_id: назначено $attributes_assigned атрибутов");
            }

        } catch (Exception $e) {
            $result['errors']++;
            $error_msg = "Ошибка импорта атрибутов на строке $line_number: " . $e->getMessage();
            error_log($error_msg);
            
            // Сохраняем детали ошибки для отображения
            if (!isset($result['error_details'])) {
                $result['error_details'] = [];
            }
            $result['error_details'][] = "Строка $line_number: " . $e->getMessage();
        }
    }

    fclose($handle);
    $result['success'] = true;
    return $result;
}

// Оптимизированная функция импорта атрибутов для больших CSV файлов
function tema_souz_import_attributes_from_csv_optimized($file_path) {
    $delimiter = isset($_POST['delimiter']) ? $_POST['delimiter'] : ',';
    $encoding = isset($_POST['encoding']) ? $_POST['encoding'] : 'utf-8';
    
    $result = [
        'success' => false,
        'processed' => 0,
        'updated' => 0,
        'errors' => 0,
        'error' => '',
        'attributes_assigned' => 0
    ];
    
    // Кэш для созданных атрибутов в рамках этого импорта
    static $created_attributes = [];

    if (!file_exists($file_path)) {
        $result['error'] = 'Файл не найден';
        return $result;
    }

    // Увеличиваем лимиты для больших файлов
    ini_set('memory_limit', '4096M');
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', -1);
    set_time_limit(0);
    
    // Отключаем автоматическое сохранение для ускорения
    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);
    
    // Отключаем буферизацию для предотвращения таймаутов
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Увеличиваем лимиты для загрузки файлов
    ini_set('upload_max_filesize', '500M');
    ini_set('post_max_size', '500M');
    ini_set('max_file_uploads', '20');

    $handle = fopen($file_path, 'r');
    if (!$handle) {
        $result['error'] = 'Не удалось открыть файл';
        return $result;
    }

    // Читаем заголовки
    $headers = fgetcsv($handle, 0, $delimiter);
    if (!$headers) {
        fclose($handle);
        $result['error'] = 'Не удалось прочитать заголовки';
        return $result;
    }

    // Конвертируем кодировку если нужно
    if ($encoding !== 'utf-8') {
        $headers = array_map(function($header) use ($encoding) {
            return mb_convert_encoding($header, 'utf-8', $encoding);
        }, $headers);
    }

    // Маппинг русских заголовков на английские
    $header_mapping = [
        'Артикул' => 'sku',
        'Полное_имя' => 'name',
        'Название' => 'name'
    ];

    // Нормализуем заголовки
    $normalized_headers = [];
    foreach ($headers as $header) {
        $header = trim($header);
        if (isset($header_mapping[$header])) {
            $normalized_headers[] = $header_mapping[$header];
        } else {
            $normalized_headers[] = $header;
        }
    }

    $line_number = 1;
    $batch_size = 10; // Уменьшаем размер пакета для предотвращения таймаутов
    $batch_count = 0;
    $attributes_batch = [];
    $last_flush_time = time();
    
    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
        $line_number++;
        
        // Конвертируем кодировку если нужно
        if ($encoding !== 'utf-8') {
            $data = array_map(function($field) use ($encoding) {
                return mb_convert_encoding($field, 'utf-8', $encoding);
            }, $data);
        }

        $result['processed']++;
        $batch_count++;

        try {
            $attribute_data = array_combine($normalized_headers, $data);
            
            // Пропускаем пустые строки
            if (empty(array_filter($data))) {
                continue;
            }
            
            // Проверяем обязательные поля
            if (empty($attribute_data['sku']) && empty($attribute_data['name'])) {
                $result['errors']++;
                continue;
            }

            // Добавляем данные в пакет
            $attributes_batch[] = $attribute_data;
            
            // Обрабатываем пакет когда он заполнен или прошло время
            $current_time = time();
            if (count($attributes_batch) >= $batch_size || ($current_time - $last_flush_time) > 30) {
                $batch_result = tema_souz_process_attributes_batch($attributes_batch, $line_number - $batch_size, $created_attributes);
                $result['updated'] += $batch_result['updated'];
                $result['errors'] += $batch_result['errors'];
                $result['attributes_assigned'] += $batch_result['attributes_assigned'];
                
                // Агрессивная очистка памяти
                $attributes_batch = [];
                wp_cache_flush();
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                // Принудительная очистка памяти
                if (function_exists('memory_get_usage') && memory_get_usage() > 1024 * 1024 * 1024) { // Больше 1GB
                    wp_cache_flush();
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }
                
                $last_flush_time = $current_time;
                
                // Сохраняем состояние импорта
                tema_souz_save_import_state($file_path, $line_number, $result);
                
                // Небольшая пауза для предотвращения перегрузки
                usleep(50000); // 0.05 секунды
            }
            
        } catch (Exception $e) {
            $result['errors']++;
            error_log("Ошибка импорта атрибутов на строке $line_number: " . $e->getMessage());
        }
    }
    
    // Обрабатываем оставшиеся данные
    if (!empty($attributes_batch)) {
        $batch_result = tema_souz_process_attributes_batch($attributes_batch, $line_number - count($attributes_batch), $created_attributes);
        $result['updated'] += $batch_result['updated'];
        $result['errors'] += $batch_result['errors'];
        $result['attributes_assigned'] += $batch_result['attributes_assigned'];
    }

    fclose($handle);
    
    // Включаем обратно автоматическое сохранение
    wp_defer_term_counting(false);
    wp_defer_comment_counting(false);
    
    $result['success'] = true;
    return $result;
}

// Функция для обработки пакета атрибутов
function tema_souz_process_attributes_batch($attributes_batch, $start_line, &$created_attributes) {
    $result = [
        'updated' => 0,
        'errors' => 0,
        'attributes_assigned' => 0
    ];
    
    foreach ($attributes_batch as $index => $attribute_data) {
        $line_number = $start_line + $index + 1;
        
        try {
            // Ищем товар по SKU или названию
            $product = null;
            if (!empty($attribute_data['sku'])) {
                $product_id = wc_get_product_id_by_sku($attribute_data['sku']);
                if ($product_id) {
                    $product = wc_get_product($product_id);
                }
            }
            
            // Если не найден по SKU, ищем по названию
            if (!$product && !empty($attribute_data['name'])) {
                $products = wc_get_products([
                    'name' => $attribute_data['name'],
                    'limit' => 1,
                    'return' => 'ids'
                ]);
                if (!empty($products)) {
                    $product = wc_get_product($products[0]);
                }
            }
            
            if (!$product) {
                $result['errors']++;
                error_log("Товар не найден (SKU: {$attribute_data['sku']}, Название: {$attribute_data['name']}) на строке $line_number");
                continue;
            }
            
            $product_id = $product->get_id();
            $attributes_assigned = 0;
            $attribute_index = 1;
            
            // Обрабатываем атрибуты
            while (isset($attribute_data["Название атрибута $attribute_index"])) {
                $attr_name = trim($attribute_data["Название атрибута $attribute_index"]);
                $attr_values = trim($attribute_data["Значение атрибута $attribute_index"]);
                
                // Пропускаем атрибуты с пустыми названиями или значениями
                if (empty($attr_name) || empty($attr_values) || $attr_name === '' || $attr_values === '') {
                    $attribute_index++;
                    continue;
                }
                
                // Создаем слаг атрибута
                $attr_slug = sanitize_title($attr_name);
                $attr_slug = 'pa_' . $attr_slug;
                
                // Проверяем кэш созданных атрибутов
                if (isset($created_attributes[$attr_name])) {
                    $attr_slug = $created_attributes[$attr_name];
                } else {
                    // Проверяем, существует ли уже такой атрибут в БД
                    global $wpdb;
                    $existing_attribute = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($existing_attribute) {
                        // Атрибут существует, используем его
                        $attr_slug = 'pa_' . $existing_attribute->attribute_name;
                        $created_attributes[$attr_name] = $attr_slug;
                    } else {
                        // Создаем новый атрибут
                        $attribute_id = wc_create_attribute([
                            'name' => $attr_name,
                            'slug' => str_replace('pa_', '', $attr_slug),
                            'type' => 'select',
                            'order_by' => 'menu_order',
                            'has_archives' => false,
                        ]);
                        
                        if ($attribute_id) {
                            $created_attributes[$attr_name] = $attr_slug;
                            
                            // Регистрируем таксономию
                            register_taxonomy($attr_slug, 'product');
                        }
                    }
                }
                
                // Разбиваем значения атрибута (могут быть через запятую)
                $attr_values_array = explode(',', $attr_values);
                $term_ids = [];
                
                foreach ($attr_values_array as $attr_val) {
                    $attr_val = trim($attr_val);
                    if (!empty($attr_val) && $attr_val !== '') {
                        // Дополнительная проверка на пустые значения
                        if (preg_match('/^[\s\-_\.]+$/', $attr_val)) {
                            continue;
                        }
                        
                        // Ищем терм по имени
                        $term = get_term_by('name', $attr_val, $attr_slug);
                        if (!$term) {
                            // Создаём новый терм
                            $term = wp_insert_term($attr_val, $attr_slug);
                            if (!is_wp_error($term)) {
                                $term_ids[] = $term['term_id'];
                            }
                        } else {
                            $term_ids[] = $term->term_id;
                        }
                    }
                }
                
                if (!empty($term_ids)) {
                    // Получаем ID атрибута из БД
                    global $wpdb;
                    $attribute_data_db = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_label = %s",
                        $attr_name
                    ));
                    
                    if ($attribute_data_db) {
                        // Создаем атрибут через WooCommerce API
                        $wc_attribute = new WC_Product_Attribute();
                        $wc_attribute->set_id($attribute_data_db->attribute_id);
                        $wc_attribute->set_name($attr_slug);
                        $wc_attribute->set_options($term_ids);
                        $wc_attribute->set_position(0);
                        $wc_attribute->set_visible(true);
                        $wc_attribute->set_variation(false);
                        
                        // Получаем существующие атрибуты товара
                        $existing_attributes = $product->get_attributes();
                        $existing_attributes[$attr_slug] = $wc_attribute;
                        
                        // Устанавливаем атрибуты товару
                        $product->set_attributes($existing_attributes);
                        $product->save();
                        
                        // Также назначаем термы через wp_set_object_terms для совместимости
                        wp_set_object_terms($product_id, $term_ids, $attr_slug);
                        
                        $attributes_assigned++;
                        $result['attributes_assigned']++;
                        
                        // Принудительно обновляем кэш
                        wp_cache_delete($product_id, 'posts');
                        clean_post_cache($product_id);
                    }
                }
                
                $attribute_index++;
            }
            
            if ($attributes_assigned > 0) {
                $result['updated']++;
            }
            
        } catch (Exception $e) {
            $result['errors']++;
            error_log("Ошибка обработки атрибутов на строке $line_number: " . $e->getMessage());
        }
    }
    
    return $result;
}

// AJAX: Поиск товаров по каталогу (поиск по названию)
add_action('wp_ajax_tema_souz_search_products', 'tema_souz_search_products');
add_action('wp_ajax_nopriv_tema_souz_search_products', 'tema_souz_search_products');
function tema_souz_search_products() {
    $term = isset($_REQUEST['term']) ? sanitize_text_field(wp_unslash($_REQUEST['term'])) : '';
    if ($term === '' || mb_strlen($term) < 2) {
        wp_send_json_success(['items' => []]);
    }

    // Сбор ID по разным стратегиям: по названию (s), по SKU (LIKE), по категории (name LIKE)
    $found_ids = [];

    // 1) Поиск по названию/контенту
    $q1 = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $term,
        'posts_per_page' => 20,
        'no_found_rows'  => true,
        'fields'         => 'ids',
    ]);
    if ($q1->have_posts()) { foreach ($q1->posts as $pid) { $found_ids[$pid] = true; } }

    // 2) Поиск по SKU (LIKE)
    global $wpdb;
    $like = '%' . $wpdb->esc_like($term) . '%';
    $sku_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_sku' AND pm.meta_value LIKE %s AND p.post_type='product' AND p.post_status='publish' LIMIT 20",
        $like
    ));
    if (!empty($sku_ids)) { foreach ($sku_ids as $pid) { $found_ids[(int) $pid] = true; } }

    // 3) Поиск по категориям (имя терма)
    $term_objs = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'name__like' => $term,
        'number'     => 10,
    ]);
    if (!is_wp_error($term_objs) && !empty($term_objs)) {
        $cat_ids = wp_list_pluck($term_objs, 'term_id');
        $q2 = new WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'no_found_rows'  => true,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_ids,
                'operator' => 'IN',
            ]],
        ]);
        if ($q2->have_posts()) { foreach ($q2->posts as $pid) { $found_ids[(int) $pid] = true; } }
    }

    // Подготовка выдачи
    $ids = array_slice(array_keys($found_ids), 0, 10);
    $items = [];
    foreach ($ids as $pid) {
        $p = wc_get_product($pid);
        if (!$p) { continue; }
        // Первая категория
        $cats = wp_get_post_terms($pid, 'product_cat', ['fields' => 'names']);
        $cat_name = (!is_wp_error($cats) && !empty($cats)) ? (string) $cats[0] : '';
        $items[] = [
            'title'   => get_the_title($pid),
            'url'     => get_permalink($pid),
            'price'   => wp_strip_all_tags($p->get_price_html()),
            'image'   => get_the_post_thumbnail_url($pid, 'woocommerce_thumbnail'),
            'instock' => $p->is_in_stock(),
            'cat'     => $cat_name,
        ];
    }
    wp_send_json_success(['items' => $items]);
}

// Обработка фильтров товаров
add_action('pre_get_posts', 'tema_souz_apply_product_filters');
function tema_souz_apply_product_filters($query) {
    // Проверяем, что это главный запрос и страница каталога товаров
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
        
        // Получаем параметры фильтров из URL
        $meta_query = [];
        $tax_query = [];
        
        // Обрабатываем все параметры фильтров
        foreach ($_GET as $key => $value) {
            if (strpos($key, 'filter_pa_') === 0) {
                $attribute_name = str_replace('filter_pa_', '', $key);
                $taxonomy = 'pa_' . $attribute_name;
                
                if (!empty($value)) {
                    $terms = is_array($value) ? $value : [$value];
                    $tax_query[] = [
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => $terms,
                        'operator' => 'IN',
                    ];
                }
            }
            
            // Обработка фильтра по цене
            if ($key === 'min_price' && !empty($value)) {
                $meta_query[] = [
                    'key'     => '_price',
                    'value'   => floatval($value),
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ];
            }
            
            if ($key === 'max_price' && !empty($value)) {
                $meta_query[] = [
                    'key'     => '_price',
                    'value'   => floatval($value),
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ];
            }
            
            // Обработка фильтра по наличию
            if ($key === 'stock_status' && !empty($value)) {
                $meta_query[] = [
                    'key'     => '_stock_status',
                    'value'   => sanitize_text_field($value),
                    'compare' => '=',
                ];
            }
        }
        
        // Применяем фильтры к запросу
        if (!empty($tax_query)) {
            $query->set('tax_query', $tax_query);
            // Отладочная информация
            error_log('Applied tax_query filters: ' . print_r($tax_query, true));
        }
        
        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
            // Отладочная информация
            error_log('Applied meta_query filters: ' . print_r($meta_query, true));
        }
        
        // Отладочная информация о GET параметрах
        if (!empty($_GET)) {
            error_log('Filter GET parameters: ' . print_r($_GET, true));
        }
    }
}

// Функция для очистки фильтров
function tema_souz_clear_filters_url() {
    $current_url = remove_query_arg(array_keys($_GET));
    return $current_url;
}

// Класс body для страниц без HERO, чтобы хедер выглядел как на single page
add_filter('body_class', function ($classes) {
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) {
        $classes[] = 'no-hero';
    }
    if (is_search() || is_404()) {
        $classes[] = 'no-hero';
    }
    // Обычные страницы без специальных шаблонов с HERO
    if (is_page() && !is_front_page() && !is_page_template('page-about.php') && !is_page_template('page-b2b.php') && !is_page_template('page-contacts.php') && !is_page_template('page-foundry.php')) {
        $classes[] = 'no-hero';
    }
    return $classes;
});


// Импорт демо-контента
require_once __DIR__ . '/inc/demo-import.php';


// Назначение изображения товара по атрибуту "Тип продукции" и категории товара
// Конфигурация соответствий: ['attribute_slug' => ['category_slug' => attachment_id]]
// Новый обработчик: назначение миниатюры по карте (атрибут + категория)
/*
add_action('save_post_product', function ($post_id, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (get_post_status($post_id) === 'auto-draft') return;
    if (!function_exists('wc_get_product')) return;

    $product = wc_get_product($post_id);
    if (!$product) return;

    // 1) Читаем новые настройки
    $type_tax = get_option('ts_img_attr_tax', '') ?: (taxonomy_exists('pa_type') ? 'pa_type' : '');
    $map = get_option('ts_img_map', []);

    // 2) Если новые не заданы, fallback на старые
    if (empty($map)) {
        $type_tax = get_option('tema_souz_image_map_tax', $type_tax);
        $legacy = get_option('tema_souz_image_map', []);
        if (is_array($legacy)) { $map = $legacy; }
    }

    if (empty($map) || !is_array($map)) return;

    // Определяем значение атрибута "Тип продукции" (берём первый терм)
    $type_term = '';
    if (taxonomy_exists($type_tax)) {
        $terms = wp_get_post_terms($post_id, $type_tax, ['fields' => 'slugs']);
        if (!is_wp_error($terms) && !empty($terms)) {
            $type_term = (string) $terms[0];
        }
    }

    if ($type_term === '' || !isset($map[$type_term]) || !is_array($map[$type_term])) return;

    // Получаем все категории товара (слаги)
    $product_cats = wp_get_post_terms($post_id, 'product_cat', ['fields' => 'slugs']);
    if (is_wp_error($product_cats) || empty($product_cats)) return;

    $chosen_attachment = 0;
    foreach ($product_cats as $cat_slug) {
        if (isset($map[$type_term][$cat_slug])) {
            $chosen_attachment = (int) ts_resolve_image_id($map[$type_term][$cat_slug]);
            break;
        }
    }

    if ($chosen_attachment <= 0) return;

    // Если у товара нет миниатюры или стоит другой id — назначаем
    $current_thumb = (int) get_post_thumbnail_id($post_id);
    if ($current_thumb === $chosen_attachment) return;

    // Проверим, что аттач существует
    $img = get_post($chosen_attachment);
    if (!$img || $img->post_type !== 'attachment') return;

    set_post_thumbnail($post_id, $chosen_attachment);
}, 10, 3);
*/

// Инструмент в админке: Инструменты → Назначить фото по типу и категории
// Новый простой инструмент: Назначение фото товаров (новый)
/* add_action('admin_menu', function () {
    add_management_page(
        'Назначить фото товаров (новый)',
        'Назначить фото (новый)',
        'manage_woocommerce',
        'assign-product-images-new',
        function () {
            if (!current_user_can('manage_woocommerce')) { wp_die('Недостаточно прав.'); }

            $notice = '';
            $type_tax = isset($_POST['type_tax']) ? sanitize_key($_POST['type_tax']) : get_option('ts_img_attr_tax', taxonomy_exists('pa_type') ? 'pa_type' : '');
            $map = get_option('ts_img_map', []);
            if (!is_array($map)) $map = [];
            $overwrite = false;

            if (isset($_POST['save_image_map']) && check_admin_referer('assign_images_map_action')) {
                $type_tax = sanitize_key($_POST['type_tax']);
                $rows = isset($_POST['rows']) && is_array($_POST['rows']) ? $_POST['rows'] : [];
                $new_map = [];
                foreach ($rows as $row) {
                    $attr = isset($row['attr']) ? sanitize_title($row['attr']) : '';
                    $cat  = isset($row['cat']) ? sanitize_title($row['cat']) : '';
                    $img_raw = isset($row['img']) ? trim((string) $row['img']) : '';
                    if ($attr && $cat && $img_raw !== '') {
                        if (!isset($new_map[$attr])) $new_map[$attr] = [];
                        $new_map[$attr][$cat] = is_numeric($img_raw) ? (int) $img_raw : esc_url_raw($img_raw);
                    }
                }
                update_option('ts_img_map', $new_map, false);
                update_option('ts_img_attr_tax', $type_tax, false);
                $map = get_option('ts_img_map', []);
                $notice = '<div class="notice notice-success"><p>Карта соответствий сохранена.</p></div>';
            }

            // Применение к товарам
            if (isset($_POST['apply_now']) && check_admin_referer('assign_images_map_action')) {
                $overwrite = !empty($_POST['overwrite']);
                $type_tax = get_option('ts_img_attr_tax', taxonomy_exists('pa_type') ? 'pa_type' : '');
                $map = get_option('ts_img_map', []);
                if (is_array($map) && !empty($map) && taxonomy_exists($type_tax) && class_exists('WC_Product')) {
                    $args = [ 'status' => ['publish','draft','private','pending'], 'limit' => -1, 'return' => 'ids' ];
                    $ids = wc_get_products($args);
                    $updated = 0;
                    foreach ($ids as $pid) {
                        // Определяем тип
                        $terms = wp_get_post_terms($pid, $type_tax, ['fields' => 'slugs']);
                        if (is_wp_error($terms) || empty($terms)) continue;
                        $type_term = (string) $terms[0];
                        if (!isset($map[$type_term]) || !is_array($map[$type_term])) continue;
                        $prod_cats = wp_get_post_terms($pid, 'product_cat', ['fields' => 'slugs']);
                        if (is_wp_error($prod_cats) || empty($prod_cats)) continue;
                        $attach = 0;
                        foreach ($prod_cats as $cat_slug) {
                            if (isset($map[$type_term][$cat_slug])) { $attach = (int) ts_resolve_image_id($map[$type_term][$cat_slug]); break; }
                        }
                        if ($attach <= 0) continue;
                        $current = (int) get_post_thumbnail_id($pid);
                        if ($current && !$overwrite) continue;
                        $img = get_post($attach);
                        if ($img && $img->post_type === 'attachment') {
                            set_post_thumbnail($pid, $attach);
                            $updated++;
                        }
                    }
                    $notice = '<div class="notice notice-success"><p>Применено. Обновлено товаров: <strong>' . intval($updated) . '</strong></p></div>';
                } else {
                    $notice = '<div class="notice notice-error"><p>Карта не задана или неверен слаг атрибута.</p></div>';
                }
            }

            // UI
            echo '<div class="wrap"><h1>Назначить фото по типу и категории</h1>' . $notice;
            echo '<form method="post">';
            wp_nonce_field('assign_images_map_action');
            echo '<table class="form-table"><tbody>';
            echo '<tr><th>Слаг атрибута "Тип продукции"</th><td><input type="text" name="type_tax" value="' . esc_attr(get_option('ts_img_attr_tax', taxonomy_exists('pa_type') ? 'pa_type' : '')) . '" class="regular-text" /> <p class="description">Например: pa_type или ваш слаг атрибута</p></td></tr>';
            echo '</tbody></table>';

            echo '<h2>Таблица соответствий</h2>';
            echo '<table class="widefat fixed" id="ts-image-map" style="max-width:1000px;">';
            echo '<thead><tr><th style="width:35%">Терм атрибута (slug)</th><th style="width:35%">Категория (slug)</th><th style="width:20%">Ссылка на изображение или ID</th><th style="width:10%"></th></tr></thead><tbody>';
            if (!empty($map)) {
                foreach ($map as $attr => $cats) {
                    if (!is_array($cats)) continue;
                    foreach ($cats as $cat => $img) {
                        echo '<tr>'
                            . '<td><input type="text" name="rows[][attr]" value="' . esc_attr($attr) . '" class="regular-text" /></td>'
                            . '<td><input type="text" name="rows[][cat]" value="' . esc_attr($cat) . '" class="regular-text" /></td>'
                            . '<td><input type="text" name="rows[][img]" value="' . esc_attr(is_scalar($img)? (string)$img : '') . '" class="regular-text" placeholder="https://... или ID" /></td>'
                            . '<td><button class="button link-delete">Удалить</button></td>'
                            . '</tr>';
                    }
                }
            }
            // Пустая строка
            echo '<tr>'
                . '<td><input type="text" name="rows[][attr]" value="" class="regular-text" /></td>'
                . '<td><input type="text" name="rows[][cat]" value="" class="regular-text" /></td>'
                . '<td><input type="text" name="rows[][img]" value="" class="regular-text" placeholder="https://... или ID" /></td>'
                . '<td><button class="button link-delete">Удалить</button></td>'
                . '</tr>';
            echo '</tbody></table>';
            echo '<p><button class="button" id="ts-add-row">Добавить строку</button></p>';
            echo '<p><button type="submit" name="save_image_map" class="button button-primary">Сохранить соответствия</button></p>';

            echo '<hr />';
            echo '<h2>Применить к товарам</h2>';
            echo '<label><input type="checkbox" name="overwrite" value="1" /> Перезаписывать существующие миниатюры</label><br><br>';
            echo '<button type="submit" name="apply_now" class="button button-primary">Применить сейчас</button>';

            echo '</form></div>';

            // JS
            wp_enqueue_script('jquery');
            wp_add_inline_script('jquery', <<<'JS'
jQuery(function($){
  $('#ts-add-row').on('click', function(e){
    e.preventDefault();
    $('#ts-image-map tbody').append('<tr>\n<td><input type="text" name="rows[][attr]" class="regular-text" /></td>\n<td><input type="text" name="rows[][cat]" class="regular-text" /></td>\n<td><input type="text" name="rows[][img]" class="regular-text" placeholder="https://... или ID" /></td>\n<td><button class="button link-delete">Удалить</button></td>\n</tr>');
  });
  $(document).on('click','.link-delete',function(e){
    e.preventDefault();
    $(this).closest('tr').remove();
  });
});
JS
            );
        }
    );
}); */

// Вспомогательно: получить attachment ID из ID или URL (при URL — загрузить в Медиа)
/* function ts_resolve_image_id($value) {
    if (is_numeric($value)) {
        $id = (int) $value;
        $p = get_post($id);
        return ($p && $p->post_type === 'attachment') ? $id : 0;
    }
    $url = is_string($value) ? trim($value) : '';
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) return 0;
    // Попробуем найти существующее вложение по GUID
    global $wpdb;
    $found = (int) $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND guid=%s LIMIT 1", $url));
    if ($found > 0) return $found;
    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    // Создадим временный пост-держатель
    $tmp_post_id = wp_insert_post(['post_title' => 'temp-image-holder', 'post_status' => 'draft', 'post_type' => 'product']);
    if (is_wp_error($tmp_post_id) || !$tmp_post_id) return 0;
    $html = media_sideload_image($url, $tmp_post_id, null, 'id');
    if (is_wp_error($html)) { wp_delete_post($tmp_post_id, true); return 0; }
    $attach_id = (int) $html;
    wp_delete_post($tmp_post_id, true);
    return $attach_id > 0 ? $attach_id : 0;
} */


// CPT: Новости и Услуги
add_action('init', function () {
    register_post_type('news', [
        'labels' => [
            'name' => __('Новости', 'tema-souz'),
            'singular_name' => __('Новость', 'tema-souz'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'news'],
        'menu_icon' => 'dashicons-megaphone',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest' => true,
        'taxonomies' => ['news_tag'], // Добавляем поддержку тегов
    ]);

	register_post_type('service', [
        'labels' => [
            'name' => __('Услуги', 'tema-souz'),
            'singular_name' => __('Услуга', 'tema-souz'),
        ],
		'public' => true,
		'has_archive' => true,
        'rewrite' => ['slug' => 'services'],
        'menu_icon' => 'dashicons-hammer',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
		'show_in_rest' => true,
	]);
    
    // Регистрация таксономии "Теги" для новостей
    register_taxonomy('news_tag', 'news', [
        'labels' => [
            'name' => __('Теги новостей', 'tema-souz'),
            'singular_name' => __('Тег', 'tema-souz'),
            'search_items' => __('Искать теги', 'tema-souz'),
            'all_items' => __('Все теги', 'tema-souz'),
            'edit_item' => __('Редактировать тег', 'tema-souz'),
            'update_item' => __('Обновить тег', 'tema-souz'),
            'add_new_item' => __('Добавить новый тег', 'tema-souz'),
            'new_item_name' => __('Название нового тега', 'tema-souz'),
            'menu_name' => __('Теги', 'tema-souz'),
        ],
        'hierarchical' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'news-tag'],
        'show_in_rest' => true,
    ]);
});

// Ассеты для CPT архивов/одиночных
add_action('wp_enqueue_scripts', function () {
    $theme_uri = get_stylesheet_directory_uri();
    $version = '1.0.1'; // Синхронизировано с основной версией
    
    if (is_post_type_archive('news') || is_tax('news_tag')) {
        wp_enqueue_style('tema-souz-blog', $theme_uri . '/assets/css/blog.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-blog', $theme_uri . '/assets/js/blog.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-blog', 'defer', true);
    }
    if (is_singular('news')) {
        wp_enqueue_style('tema-souz-news-article', $theme_uri . '/assets/css/news-article.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-news-article', $theme_uri . '/assets/js/news-article.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-news-article', 'defer', true);
    }
    if (is_post_type_archive('service')) {
        wp_enqueue_style('tema-souz-services', $theme_uri . '/assets/css/services.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-services', $theme_uri . '/assets/js/services.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-services', 'defer', true);
    }
    if (is_singular('service')) {
        wp_enqueue_style('tema-souz-service-detail', $theme_uri . '/assets/css/service-detail.css', ['tema-souz-styles'], $version);
        wp_enqueue_script('tema-souz-service-detail', $theme_uri . '/assets/js/service-detail.js', ['tema-souz-script'], $version, true);
        wp_script_add_data('tema-souz-service-detail', 'defer', true);
    }
    if (is_page_template('page-foundry.php')) {
        wp_enqueue_style('tema-souz-service-detail', $theme_uri . '/assets/css/service-detail.css', ['tema-souz-styles'], $version);
        wp_enqueue_style('tema-souz-foundry', $theme_uri . '/assets/css/foundry.css', ['tema-souz-service-detail'], $version);
    }
    if (is_singular('service')) {
        wp_enqueue_style('tema-souz-foundry', $theme_uri . '/assets/css/foundry.css', ['tema-souz-styles'], $version);
    }
});

// Дополнительные поля CF для CPT
add_action('carbon_fields_register_fields', function () {
    if (!class_exists('Carbon_Fields\Container')) return;

    \Carbon_Fields\Container::make('post_meta', __('Новость: настройки', 'tema-souz'))
        ->where('post_type', '=', 'news')
        ->add_fields([
            \Carbon_Fields\Field::make('image', 'news_header_image', 'Изображение в статье'),
        ]);

    \Carbon_Fields\Container::make('post_meta', __('Услуга: настройки', 'tema-souz'))
        ->where('post_type', '=', 'service')
        ->add_fields([
            \Carbon_Fields\Field::make('text', 'service_hero_subtitle', 'Подзаголовок в герое')->set_default_value('В СПБ И ОБЛАСТИ'),
            \Carbon_Fields\Field::make('text', 'service_cta_label', 'Текст кнопки')->set_default_value('ЗАКАЗАТЬ УСЛУГУ'),
            \Carbon_Fields\Field::make('rich_text', 'service_desc', 'Описание услуги'),
            \Carbon_Fields\Field::make('complex', 'service_cost_rows', 'Стоимость (строки)')
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'thickness', 'Толщина'),
                    \Carbon_Fields\Field::make('text', 'price', 'Стоимость'),
                    \Carbon_Fields\Field::make('text', 'min_volume', 'Минимальный объем'),
                ]),
            \Carbon_Fields\Field::make('text', 'service_features_title', 'Заголовок тех. особенностей')->set_default_value('Технологические особенности лазерной резки'),
            \Carbon_Fields\Field::make('rich_text', 'service_features_text', 'Текст тех. особенностей'),
            \Carbon_Fields\Field::make('text', 'service_advantages_title', 'Заголовок преимуществ')->set_default_value('Преимущества лазерной резки'),
            \Carbon_Fields\Field::make('rich_text', 'service_advantages_text', 'Текст преимуществ'),
            \Carbon_Fields\Field::make('complex', 'service_faq', 'FAQ')
                ->add_fields([
                    \Carbon_Fields\Field::make('text', 'q', 'Вопрос'),
                    \Carbon_Fields\Field::make('rich_text', 'a', 'Ответ'),
                ]),
        ]);

    // Настройки для таксономии product_cat (WooCommerce категории): HERO изображение
    if (taxonomy_exists('product_cat')) {
        \Carbon_Fields\Container::make('term_meta', __('Категория каталога: настройки', 'tema-souz'))
            ->where('term_taxonomy', '=', 'product_cat')
            ->add_fields([
                \Carbon_Fields\Field::make('image', 'product_cat_hero_image', 'HERO изображение категории'),
            ]);
    }
});

// Принудительная загрузка WooCommerce скриптов на страницах товаров
add_action('wp_enqueue_scripts', function() {
    if (is_product() || is_shop() || is_product_category() || is_product_tag()) {
        // Принудительно загружаем WooCommerce скрипты
        if (function_exists('WC')) {
            WC()->frontend_includes();
            wp_enqueue_script('wc-add-to-cart');
            wp_enqueue_script('wc-cart-fragments');
        }
    }
}, 5);

// Принудительная загрузка WooCommerce скриптов через wp_head
add_action('wp_head', function() {
    if (is_product() && function_exists('WC')) {
        ?>
        <script type="text/javascript">
        // Принудительно загружаем WooCommerce скрипты
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                if (!$.fn.wc_cart_fragments) {
                    $.getScript('<?php echo esc_js(WC()->plugin_url() . '/assets/js/frontend/cart-fragments.min.js'); ?>');
                }
                if (!$.fn.wc_add_to_cart) {
                    $.getScript('<?php echo esc_js(WC()->plugin_url() . '/assets/js/frontend/add-to-cart.min.js'); ?>');
                }
            });
        }
        </script>
        <?php
    }
}, 1);

// Принудительная загрузка WooCommerce параметров
add_action('wp_footer', function() {
    if (is_product() && function_exists('WC')) {
        ?>
        <script type="text/javascript">
        if (typeof wc_add_to_cart_params === 'undefined') {
            window.wc_add_to_cart_params = {
                wc_ajax_url: '<?php echo esc_js(home_url('/?wc-ajax=%%endpoint%%')); ?>',
                wc_ajax_nonce: '<?php echo esc_js(wp_create_nonce('wc_add_to_cart')); ?>',
                i18n_view_cart: '<?php echo esc_js(__('View cart', 'woocommerce')); ?>',
                cart_url: '<?php echo esc_js(wc_get_cart_url()); ?>',
                is_cart: <?php echo is_cart() ? 'true' : 'false'; ?>,
                cart_redirect_after_add: <?php echo get_option('woocommerce_cart_redirect_after_add') ? 'true' : 'false'; ?>
            };
        }
        // Принудительно устанавливаем nonce
        if (window.wc_add_to_cart_params && !window.wc_add_to_cart_params.wc_ajax_nonce) {
            window.wc_add_to_cart_params.wc_ajax_nonce = '<?php echo esc_js(wp_create_nonce('wc_add_to_cart')); ?>';
        }
        // Принудительно загружаем cart-fragments
        if (typeof jQuery !== 'undefined' && jQuery.fn && !jQuery.fn.wc_cart_fragments) {
            jQuery.getScript('<?php echo esc_js(WC()->plugin_url() . '/assets/js/frontend/cart-fragments.min.js'); ?>');
        }
        </script>
        <?php
    }
}, 1);

// Обновление бейджа корзины через фрагменты WooCommerce
add_filter('woocommerce_add_to_cart_fragments', function($fragments){
    if (function_exists('WC') && WC()->cart) {
        $count = WC()->cart->get_cart_contents_count();
    } else {
        $count = 0;
    }
    $fragments['.cart-badge'] = '<span class="cart-badge">' . intval($count) . '</span>';
    return $fragments;
});

// Принудительно отключаем вариации для всех товаров
add_filter('woocommerce_product_needs_processing', '__return_false');
add_filter('woocommerce_is_purchasable', '__return_true');
add_filter('woocommerce_add_to_cart_validation', function($valid, $product_id, $quantity) {
    return true; // Всегда разрешаем добавление в корзину
}, 10, 3);

// Отключаем требование выбора вариаций
add_filter('woocommerce_add_to_cart_validation', function($valid, $product_id, $quantity, $variation_id = 0, $variations = array()) {
    return true;
}, 20, 5);

// Отключаем проверку цены для добавления в корзину
add_filter('woocommerce_product_get_price', function($price, $product) {
    if (empty($price)) {
        return 0; // Устанавливаем цену 0 если её нет
    }
    return $price;
}, 10, 2);

add_filter('woocommerce_product_get_regular_price', function($price, $product) {
    if (empty($price)) {
        return 0; // Устанавливаем цену 0 если её нет
    }
    return $price;
}, 10, 2);

add_filter('woocommerce_product_get_sale_price', function($price, $product) {
    if (empty($price)) {
        return 0; // Устанавливаем цену 0 если её нет
    }
    return $price;
}, 10, 2);

// Отключаем проверку наличия цены при добавлении в корзину
add_filter('woocommerce_add_to_cart_validation', function($valid, $product_id, $quantity, $variation_id = 0, $variations = array()) {
    // Принудительно устанавливаем цену если её нет
    $product = wc_get_product($product_id);
    if ($product && empty($product->get_price())) {
        update_post_meta($product_id, '_price', '0');
        update_post_meta($product_id, '_regular_price', '0');
    }
    return true;
}, 30, 5);

// Отключаем проверку цены в валидации товара
add_filter('woocommerce_product_is_purchasable', function($purchasable, $product) {
    return true; // Всегда разрешаем покупку
}, 10, 2);

// ===== СИСТЕМА НАСТРОЙКИ ЗАВИСИМОСТЕЙ АТРИБУТОВ =====

// Создание таблицы для хранения настроек зависимостей атрибутов
function create_attribute_dependencies_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'attribute_dependencies';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_type varchar(100) NOT NULL,
        primary_attribute varchar(100) NOT NULL,
        secondary_attribute varchar(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_dependency (product_type, primary_attribute, secondary_attribute)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Активация таблицы при активации темы
add_action('after_switch_theme', 'create_attribute_dependencies_table');

// Добавление страницы настроек в админку
add_action('admin_menu', 'add_attribute_dependencies_admin_menu');

function add_attribute_dependencies_admin_menu() {
    add_submenu_page(
        'woocommerce',
        'Зависимости атрибутов',
        'Зависимости атрибутов',
        'manage_woocommerce',
        'attribute-dependencies',
        'attribute_dependencies_admin_page'
    );
    
    // Добавляем страницу диагностики
    add_management_page(
        'Диагностика атрибутов',
        'Диагностика атрибутов',
        'manage_woocommerce',
        'debug-attributes',
        'debug_attributes_page'
    );
}

// Страница диагностики атрибутов
function debug_attributes_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Недостаточно прав для доступа к этой странице');
    }

    echo '<div class="wrap">';
    echo '<h1>Диагностика атрибутов WooCommerce</h1>';

    // Получаем все атрибуты WooCommerce
    $attributes = wc_get_attribute_taxonomies();
    echo '<h2>Все атрибуты WooCommerce:</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Название</th><th>Слаг</th><th>Тип</th><th>Порядок</th></tr></thead>';
    echo '<tbody>';

    if (empty($attributes)) {
        echo '<tr><td colspan="5">Атрибуты не найдены</td></tr>';
    } else {
        foreach ($attributes as $attr) {
            echo '<tr>';
            echo '<td>' . esc_html($attr->attribute_id) . '</td>';
            echo '<td>' . esc_html($attr->attribute_label) . '</td>';
            echo '<td>pa_' . esc_html($attr->attribute_name) . '</td>';
            echo '<td>' . esc_html($attr->attribute_type) . '</td>';
            echo '<td>' . esc_html($attr->attribute_orderby) . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table>';

    // Проверяем возможные атрибуты типа проката
    echo '<h2>Проверка атрибутов типа проката:</h2>';
    $possible_type_attributes = ['pa_product_type', 'pa_type', 'pa_tip_prokata', 'pa_tip_produkcii'];

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Атрибут</th><th>Существует</th><th>Количество терминов</th></tr></thead>';
    echo '<tbody>';

    foreach ($possible_type_attributes as $attr) {
        $exists = taxonomy_exists($attr);
        $term_count = 0;
        
        if ($exists) {
            $terms = get_terms(array(
                'taxonomy' => $attr,
                'hide_empty' => false,
            ));
            $term_count = is_wp_error($terms) ? 0 : count($terms);
        }
        
        echo '<tr>';
        echo '<td>' . esc_html($attr) . '</td>';
        echo '<td>' . ($exists ? '✅ Да' : '❌ Нет') . '</td>';
        echo '<td>' . $term_count . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    // Если есть атрибуты, показываем их термины
    if (!empty($attributes)) {
        echo '<h2>Термины для каждого атрибута:</h2>';
        
        foreach ($attributes as $attr) {
            $taxonomy = 'pa_' . $attr->attribute_name;
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            
            if (!is_wp_error($terms) && !empty($terms)) {
                echo '<h3>' . esc_html($attr->attribute_label) . ' (' . $taxonomy . ')</h3>';
                echo '<ul>';
                foreach ($terms as $term) {
                    echo '<li>' . esc_html($term->name) . ' (slug: ' . esc_html($term->slug) . ')</li>';
                }
                echo '</ul>';
            }
        }
    }

    echo '</div>';
}

// Страница настроек зависимостей атрибутов
function attribute_dependencies_admin_page() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'attribute_dependencies';
    
    // Обработка AJAX запросов
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_table') {
            // Проверяем nonce для безопасности
            if (!wp_verify_nonce($_POST['_wpnonce'], 'create_table_action')) {
                echo '<div class="notice notice-error"><p>Ошибка безопасности!</p></div>';
            } else {
                create_attribute_dependencies_table();
                echo '<div class="notice notice-success"><p>Таблица создана успешно!</p></div>';
            }
        }
        if ($_POST['action'] === 'save_dependency') {
            // Проверяем nonce для безопасности
            if (!wp_verify_nonce($_POST['_wpnonce'], 'save_dependency_action')) {
                echo '<div class="notice notice-error"><p>Ошибка безопасности!</p></div>';
            } else {
                // Обрабатываем product_type с учетом кодировки
                $product_type = '';
                if (isset($_POST['product_type'])) {
                    $raw_type = $_POST['product_type'];
                    // Если это URL-кодированная строка, декодируем
                    if (strpos($raw_type, '%') !== false) {
                        $product_type = rawurldecode($raw_type);
                    } else {
                        $product_type = $raw_type;
                    }
                    $product_type = sanitize_text_field($product_type);
                }
                
                $primary_attr = isset($_POST['primary_attribute']) ? sanitize_text_field($_POST['primary_attribute']) : '';
                $secondary_attr = isset($_POST['secondary_attribute']) ? sanitize_text_field($_POST['secondary_attribute']) : '';
                
                // Отладочная информация
                echo '<div class="notice notice-info">';
                echo '<p><strong>Отладка:</strong></p>';
                echo '<p>Исходный product_type: "' . esc_html($_POST['product_type'] ?? 'НЕ УСТАНОВЛЕНО') . '"</p>';
                echo '<p>Обработанный product_type: "' . esc_html($product_type) . '"</p>';
                echo '<p>primary_attr: "' . esc_html($primary_attr) . '"</p>';
                echo '<p>secondary_attr: "' . esc_html($secondary_attr) . '"</p>';
                echo '<p>Длина product_type: ' . strlen($product_type) . '</p>';
                echo '</div>';
                
                // Проверяем, что все поля заполнены
                if (empty($product_type) || empty($primary_attr) || empty($secondary_attr)) {
                    echo '<div class="notice notice-error"><p>Все поля должны быть заполнены!</p>';
                    echo '<p>Полученные данные: product_type="' . esc_html($product_type) . '", primary_attr="' . esc_html($primary_attr) . '", secondary_attr="' . esc_html($secondary_attr) . '"</p>';
                    echo '<p>POST данные: ' . print_r($_POST, true) . '</p></div>';
                } else {
                    // Проверяем, что таблица существует
                    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
                    if (!$table_exists) {
                        echo '<div class="notice notice-error"><p>Таблица не существует! Попробуйте переключить тему.</p></div>';
                    } else {
                        $result = $wpdb->replace(
                            $table_name,
                            array(
                                'product_type' => $product_type,
                                'primary_attribute' => $primary_attr,
                                'secondary_attribute' => $secondary_attr
                            ),
                            array('%s', '%s', '%s')
                        );
                        
                        if ($result !== false) {
                            echo '<div class="notice notice-success"><p>Зависимость сохранена! ID: ' . $result . '</p></div>';
                        } else {
                            echo '<div class="notice notice-error"><p>Ошибка при сохранении: ' . $wpdb->last_error . '</p></div>';
                        }
                    }
                }
            }
        }
        
        if ($_POST['action'] === 'delete_dependency') {
            // Проверяем nonce для безопасности
            if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_dependency_action')) {
                echo '<div class="notice notice-error"><p>Ошибка безопасности!</p></div>';
            } else {
                $id = intval($_POST['dependency_id']);
                if ($id > 0) {
                    $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
                    
                    if ($result !== false) {
                        echo '<div class="notice notice-success"><p>Зависимость удалена!</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>Ошибка при удалении: ' . $wpdb->last_error . '</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error"><p>Неверный ID зависимости!</p></div>';
                }
            }
        }
    }
    
    // Получение всех зависимостей
    $dependencies = $wpdb->get_results("SELECT * FROM $table_name ORDER BY product_type, primary_attribute");
    
    // Получение всех типов товаров (атрибуты)
    $product_attributes = wc_get_attribute_taxonomies();
    $attribute_options = array();
    foreach ($product_attributes as $attr) {
        $attribute_options['pa_' . $attr->attribute_name] = $attr->attribute_label;
    }
    
    // Получение всех терминов для типов проката
    // Сначала попробуем найти атрибут типа проката
    $type_taxonomy = null;
    $possible_type_attributes = ['pa_product_type', 'pa_type', 'pa_tip_prokata', 'pa_tip_produkcii'];
    
    foreach ($possible_type_attributes as $attr) {
        if (taxonomy_exists($attr)) {
            $type_taxonomy = $attr;
            break;
        }
    }
    
    // Если не нашли стандартные, ищем все атрибуты и берем первый
    if (!$type_taxonomy) {
        $all_attributes = wc_get_attribute_taxonomies();
        if (!empty($all_attributes)) {
            $type_taxonomy = 'pa_' . $all_attributes[0]->attribute_name;
        }
    }
    
    $product_types = array();
    if ($type_taxonomy) {
        $product_types = get_terms(array(
            'taxonomy' => $type_taxonomy,
            'hide_empty' => false,
        ));
    }
    
    ?>
    <div class="wrap">
        <h1>Настройка зависимостей атрибутов</h1>
        <p>Настройте, какие атрибуты должны отображаться для каждого типа проката.</p>
        
        <?php if ($type_taxonomy): ?>
            <div class="notice notice-info">
                <p><strong>Найденный атрибут типа проката:</strong> <?php echo esc_html($type_taxonomy); ?></p>
                <p>Найдено терминов: <?php echo count($product_types); ?></p>
                <p>Найдено атрибутов: <?php echo count($attribute_options); ?></p>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <p><strong>Внимание:</strong> Не найден атрибут типа проката. Убедитесь, что атрибуты товаров созданы в WooCommerce.</p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($product_types)): ?>
            <div class="notice notice-warning">
                <p><strong>Внимание:</strong> Не найдено типов проката. Убедитесь, что у товаров назначены термины атрибута типа.</p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($attribute_options)): ?>
            <div class="notice notice-warning">
                <p><strong>Внимание:</strong> Не найдено атрибутов товаров. Убедитесь, что атрибуты созданы в WooCommerce.</p>
            </div>
        <?php endif; ?>
        
        <?php 
        // Проверяем существование таблицы
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if (!$table_exists): ?>
            <div class="notice notice-error">
                <p><strong>Ошибка:</strong> Таблица зависимостей не существует!</p>
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('create_table_action'); ?>
                    <input type="hidden" name="action" value="create_table">
                    <input type="submit" class="button button-primary" value="Создать таблицу">
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Форма добавления новой зависимости -->
        <div class="card" style="max-width: 600px; margin-bottom: 20px;">
            <h2>Добавить зависимость</h2>
            <form method="post" action="">
                <?php wp_nonce_field('save_dependency_action'); ?>
                <input type="hidden" name="action" value="save_dependency">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="product_type">Тип проката</label>
                        </th>
                        <td>
                            <select name="product_type" id="product_type" required>
                                <option value="">Выберите тип проката</option>
                                <?php foreach ($product_types as $type): ?>
                                    <option value="<?php echo esc_attr($type->name); ?>">
                                        <?php echo esc_html($type->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="primary_attribute">Первый атрибут</label>
                        </th>
                        <td>
                            <select name="primary_attribute" id="primary_attribute" required>
                                <option value="">Выберите первый атрибут</option>
                                <?php foreach ($attribute_options as $attr_slug => $attr_label): ?>
                                    <option value="<?php echo esc_attr($attr_slug); ?>">
                                        <?php echo esc_html($attr_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="secondary_attribute">Второй атрибут</label>
                        </th>
                        <td>
                            <select name="secondary_attribute" id="secondary_attribute" required>
                                <option value="">Выберите второй атрибут</option>
                                <?php foreach ($attribute_options as $attr_slug => $attr_label): ?>
                                    <option value="<?php echo esc_attr($attr_slug); ?>">
                                        <?php echo esc_html($attr_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Сохранить зависимость'); ?>
            </form>
        </div>
        
        <!-- Список существующих зависимостей -->
        <div class="card">
            <h2>Текущие зависимости</h2>
            <?php if (empty($dependencies)): ?>
                <p>Зависимости не настроены.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Тип проката</th>
                            <th>Первый атрибут</th>
                            <th>Второй атрибут</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dependencies as $dep): ?>
                            <tr>
                                <td><?php echo esc_html($dep->product_type); ?></td>
                                <td><?php echo esc_html($attribute_options[$dep->primary_attribute] ?? $dep->primary_attribute); ?></td>
                                <td><?php echo esc_html($attribute_options[$dep->secondary_attribute] ?? $dep->secondary_attribute); ?></td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('delete_dependency_action'); ?>
                                        <input type="hidden" name="action" value="delete_dependency">
                                        <input type="hidden" name="dependency_id" value="<?php echo esc_attr($dep->id); ?>">
                                        <input type="submit" class="button button-small" value="Удалить" 
                                               onclick="return confirm('Вы уверены, что хотите удалить эту зависимость?');">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        padding: 20px;
    }
    .form-table th {
        width: 200px;
    }
    </style>
    <?php
}

// Функция для получения зависимостей атрибутов
function get_attribute_dependencies($product_type = null) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'attribute_dependencies';
    
    // Проверяем существование таблицы
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    error_log('DEBUG: Table ' . $table_name . ' exists: ' . ($table_exists ? 'YES' : 'NO'));
    
    if (!$table_exists) {
        error_log('DEBUG: Table does not exist, returning empty array');
        return [];
    }
    
    if ($product_type) {
        $dependencies = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE product_type = %s",
            $product_type
        ));
        error_log('DEBUG: Found ' . count($dependencies) . ' dependencies for type: ' . $product_type);
    } else {
        $dependencies = $wpdb->get_results("SELECT * FROM $table_name");
        error_log('DEBUG: Found ' . count($dependencies) . ' total dependencies');
    }
    
    error_log('DEBUG: Dependencies: ' . wp_json_encode($dependencies));
    
    return $dependencies;
}

// Функция для получения настроек видимости атрибутов для фронтенда
function get_attribute_visibility_config() {
    $dependencies = get_attribute_dependencies();
    
    // Найдем правильный атрибут типа проката
    $type_taxonomy = null;
    $possible_type_attributes = ['pa_product_type', 'pa_type', 'pa_tip_prokata', 'pa_tip_produkcii'];
    
    foreach ($possible_type_attributes as $attr) {
        if (taxonomy_exists($attr)) {
            $type_taxonomy = $attr;
            break;
        }
    }
    
    // Если не нашли стандартные, ищем все атрибуты и берем первый
    if (!$type_taxonomy) {
        $all_attributes = wc_get_attribute_taxonomies();
        if (!empty($all_attributes)) {
            $type_taxonomy = 'pa_' . $all_attributes[0]->attribute_name;
        }
    }
    
    $config = array(
        'type_attr' => $type_taxonomy ?: 'pa_product_type',
        'rules' => array()
    );
    
    foreach ($dependencies as $dep) {
        $config['rules'][] = array(
            'type' => $dep->product_type,
            'show' => array($dep->primary_attribute, $dep->secondary_attribute),
            'hide' => array()
        );
    }
    
    return $config;
}

// Добавление конфигурации в фронтенд
add_action('wp_head', 'add_attribute_visibility_config_to_frontend');

// Форсируем загрузку шаблона single product из папки woocommerce
add_filter('template_include', function($template){
    if (function_exists('is_product') && is_product()) {
        $theme_path = get_stylesheet_directory();
        $wc_single = $theme_path . '/woocommerce/single-product.php';
        if (file_exists($wc_single)) {
            return $wc_single;
        }
    }
    return $template;
}, 50);

// Система вариаций товаров
function get_product_variations($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) {
        return [];
    }
    
    // Получаем конфигурацию зависимостей
    $config = get_attribute_visibility_config();
    if (!$config || !$config['rules']) {
        return [];
    }
    
    // Определяем тип товара
    $product_type = get_product_type_from_title($product->get_name());
    if (!$product_type) {
        return [];
    }
    
    // Находим правило для этого типа
    $rule = null;
    foreach ($config['rules'] as $r) {
        if ($r['type'] === $product_type) {
            $rule = $r;
            break;
        }
    }
    
    if (!$rule) {
        return [];
    }
    
    // Получаем атрибуты для отображения
    $attributes_to_show = $rule['show'];
    
    // Получаем все товары того же типа
    $variations = get_products_by_type($product_type, $product_id);
    
    // Группируем по атрибутам
    $grouped_variations = [];
    foreach ($variations as $variation) {
        $variation_product = wc_get_product($variation);
        if (!$variation_product) continue;
        
        $attributes = $variation_product->get_attributes();
        $variation_data = [
            'id' => $variation,
            'name' => $variation_product->get_name(),
            'url' => $variation_product->get_permalink(),
            'attributes' => []
        ];
        
        foreach ($attributes_to_show as $attr_slug) {
            if (isset($attributes[$attr_slug])) {
                $attr = $attributes[$attr_slug];
                $options = $attr->get_options();
                if (!empty($options)) {
                    $variation_data['attributes'][$attr_slug] = $options[0];
                }
            }
        }
        
        $grouped_variations[] = $variation_data;
    }
    
    return $grouped_variations;
}

// Функция нормализации типа товара для сопоставления
function normalize_product_type($type_value) {
    if (!$type_value) {
        return null;
    }
    
    $type_lower = mb_strtolower(trim($type_value), 'UTF-8');
    
        // Возвращаем исходное значение без изменений для точного сопоставления с базой данных
        return $type_value;
}

// Функция определения типа товара из атрибута "тип проката"
function get_product_type_from_attributes($product) {
    if (!$product) {
        return null;
    }
    
    // Возможные названия атрибута "тип проката" (в порядке приоритета)
    $type_attributes = [
        'pa_product_type',  // Приоритет 1: pa_product_type
        'pa_tip_prokata',   // Приоритет 2: тип проката
        'pa_tip_produkcii', // Приоритет 3: тип продукции
        'pa_type',          // Приоритет 4: тип
        'pa_tip'            // Приоритет 5: тип (сокращенный)
    ];
    
    $attributes = $product->get_attributes();
    
        foreach ($type_attributes as $attr_name) {
            if (isset($attributes[$attr_name])) {
                $attr = $attributes[$attr_name];
                if ($attr->is_taxonomy()) {
                    $terms = wp_get_post_terms($product->get_id(), $attr_name);
                    if (!empty($terms) && !is_wp_error($terms)) {
                        // Возвращаем значение атрибута (тип проката)
                        return $terms[0]->name;
                    }
                } else {
                    $options = $attr->get_options();
                    if (!empty($options)) {
                        // Возвращаем значение атрибута (тип проката)
                        return $options[0];
                    }
                }
            }
        }
    
    return null;
}

// Функция определения типа товара из заголовка (fallback)
function get_product_type_from_title($title) {
    $title_lower = mb_strtolower($title, 'UTF-8');
    
    $types = [
        'шина' => 'шина',
        'труба' => 'труба', 
        'лист' => 'лист',
        'проволока' => 'проволока',
        'пруток' => 'пруток',
        'лента' => 'лента',
        'круг' => 'круг',
        'квадрат' => 'квадрат',
        'шестигранник' => 'шестигранник',
        'уголок' => 'уголок',
        'швеллер' => 'швеллер',
        'двутавр' => 'двутавр',
        'балка' => 'балка',
        'профиль' => 'профиль',
        'полоса' => 'полоса',
        'катанка' => 'катанка',
        'арматура' => 'арматура',
        'сетка' => 'сетка',
        'электрод' => 'электрод',
        'электроды' => 'электрод',
        'сварочная' => 'сварочная',
        'сварочные' => 'сварочная'
    ];
    
    foreach ($types as $keyword => $type) {
        if (strpos($title_lower, $keyword) !== false) {
            return $type;
        }
    }
    
    return null;
}

// Функция получения товаров того же типа
function get_products_by_type($type, $exclude_id = null) {
    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_visibility',
                'value' => ['visible', 'catalog'],
                'compare' => 'IN'
            ]
        ]
    ];
    
    if ($exclude_id) {
        $args['post__not_in'] = [$exclude_id];
    }
    
    $products = get_posts($args);
    $filtered_products = [];
    
    foreach ($products as $product) {
        $wc_product = wc_get_product($product->ID);
        if (!$wc_product) continue;
        
        // Сначала пробуем определить тип по атрибутам, затем по заголовку
        $product_type = get_product_type_from_attributes($wc_product);
        if (!$product_type) {
        $product_type = get_product_type_from_title($product->post_title);
        }
        
        if ($product_type === $type) {
            $filtered_products[] = $product->ID;
        }
    }
    
    return $filtered_products;
}

// Функция отображения вариаций
function display_product_variations() {
    global $product;
    
    // Отладочная информация
    echo '<!-- DEBUG: display_product_variations called -->';
    
    if (!$product) {
        echo '<!-- DEBUG: No product object -->';
        return;
    }
    
    $product_id = $product->get_id();
    echo '<!-- DEBUG: Product ID: ' . $product_id . ' -->';
    
    $variations = get_product_variations($product_id);
    echo '<!-- DEBUG: Variations count: ' . count($variations) . ' -->';
    
    if (empty($variations)) {
        echo '<!-- DEBUG: No variations found -->';
        return;
    }
    
    // Получаем конфигурацию зависимостей
    $config = get_attribute_visibility_config();
    $product_type = get_product_type_from_title($product->get_name());
    
    $rule = null;
    if ($config && $config['rules']) {
        foreach ($config['rules'] as $r) {
            if ($r['type'] === $product_type) {
                $rule = $r;
                break;
            }
        }
    }
    
    if (!$rule) {
        return;
    }
    
    $attributes_to_show = $rule['show'];
    
    // Маппинг slug'ов на русские названия
    $attr_names = [
        'pa_marka_medi' => 'Марка меди',
        'pa_gost_na_prokat' => 'ГОСТ на прокат',
        'pa_steel' => 'Марка стали',
        'pa_material' => 'Материал',
        'pa_thickness' => 'Толщина',
        'pa_diameter' => 'Диаметр'
    ];
    
    echo '<div class="product-variations">';
    echo '<h3>Вариации товара</h3>';
    
    // Группируем вариации по атрибутам
    $grouped = [];
    foreach ($variations as $variation) {
        foreach ($attributes_to_show as $attr_slug) {
            if (isset($variation['attributes'][$attr_slug])) {
                $value = $variation['attributes'][$attr_slug];
                if (!isset($grouped[$attr_slug])) {
                    $grouped[$attr_slug] = [];
                }
                if (!isset($grouped[$attr_slug][$value])) {
                    $grouped[$attr_slug][$value] = [];
                }
                $grouped[$attr_slug][$value][] = $variation;
            }
        }
    }
    
    // Отображаем группы атрибутов
    foreach ($attributes_to_show as $attr_slug) {
        if (!isset($grouped[$attr_slug])) continue;
        
        $attr_name = $attr_names[$attr_slug] ?? $attr_slug;
        echo '<div class="variation-group">';
        echo '<h4>' . esc_html($attr_name) . ':</h4>';
        echo '<div class="variation-options">';
        
        foreach ($grouped[$attr_slug] as $value => $variation_list) {
            $is_current = false;
            
            // Проверяем, является ли это текущим товаром
            foreach ($variation_list as $variation) {
                if ($variation['id'] == $product_id) {
                    $is_current = true;
                    break;
                }
            }
            
            $button_class = $is_current ? 'variation-option current' : 'variation-option';
            
            echo '<a href="' . esc_url($variation_list[0]['url']) . '" class="' . $button_class . '">';
            echo esc_html($value);
            echo '</a>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}


// Система вариаций теперь интегрирована в woocommerce/single-product.php




function add_attribute_visibility_config_to_frontend() {
    if (is_product()) {
        $config = get_attribute_visibility_config();
        echo '<script>window.TSAttrVisibility = ' . wp_json_encode($config) . ';</script>';
        echo '<script>console.log("TSAttrVisibility config loaded:", window.TSAttrVisibility);</script>';
        
        // Убираем принудительное скрытие групп атрибутов: позволяем вариациям отображаться по умолчанию
        echo '<style id="force-attribute-visibility"></style>';
        
        // Умный JavaScript для управления видимостью
        echo '<script>
        (function() {
            console.log("=== SMART ATTRIBUTE VISIBILITY INIT ===");
            
            function smartApplyVisibility() {
                console.log("Smart applying visibility...");
                
                // Анализируем структуру страницы
                const analysis = {
                    forms: document.querySelectorAll("form").length,
                    attributeGroups: document.querySelectorAll(".thickness-options, .product-card__thickness-options, [data-attr-select]").length,
                    productInfo: document.querySelectorAll("h1, h2, h3, .product-title, .entry-title").length
                };
                console.log("Page analysis:", analysis);
                
                // Скрываем только группы атрибутов с опциями
                const attributeGroups = document.querySelectorAll(".thickness-options, .product-card__thickness-options, [data-attr-select]");
                attributeGroups.forEach(group => {
                    const hasOptions = group.querySelectorAll("input, select, button").length > 0;
                    if (hasOptions) {
                        group.style.display = "none !important";
                        group.style.visibility = "hidden !important";
                        group.setAttribute("data-smart-hidden", "true");
                        console.log("Smart hidden group with options:", group.className || group.tagName);
                    }
                });
                
                // Если есть конфигурация, применяем правила
                if (window.TSAttrVisibility && window.TSAttrVisibility.rules) {
                    const rules = window.TSAttrVisibility.rules;
                    console.log("Applying rules:", rules);
                    
                    // Определяем тип товара из заголовка
                    const title = document.querySelector("h1, .product-title, .entry-title");
                    let productType = null;
                    if (title) {
                        const titleText = title.textContent.toLowerCase();
                        if (titleText.includes("шина")) productType = "шина";
                        else if (titleText.includes("труба")) productType = "труба";
                        else if (titleText.includes("лист")) productType = "лист";
                        else if (titleText.includes("круг")) productType = "круг";
                        else if (titleText.includes("проволока")) productType = "проволока";
                    }
                    
                    console.log("Detected product type:", productType);
                    
                    if (productType) {
                        const rule = rules.find(r => r.type === productType);
                        if (rule) {
                            console.log("Found rule for type:", productType, rule);
                            
                            // Показываем нужные группы
                            rule.show.forEach(attrSlug => {
                                console.log("Trying to show group for:", attrSlug);
                                
                                // Расширенный поиск элементов
                                const selectors = [
                                    `[data-attr-select="${attrSlug}"]`,
                                    `.thickness-options`,
                                    `.product-card__thickness-options`,
                                    `.product-card__section`,
                                    `.variations`,
                                    `.woocommerce-variations`
                                ];
                                
                                let found = false;
                                selectors.forEach(selector => {
                                    const elements = document.querySelectorAll(selector);
                                    elements.forEach(el => {
                                        el.style.display = "";
                                        el.style.visibility = "";
                                        el.removeAttribute("data-smart-hidden");
                                        console.log("Shown:", selector, el);
                                        found = true;
                                    });
                                });
                                
                                // Если не найдено по селекторам, ищем по тексту
                                if (!found) {
                                    const headings = document.querySelectorAll("h1, h2, h3, h4, h5, h6");
                                    headings.forEach(heading => {
                                        const text = heading.textContent.toLowerCase();
                                        const attrMappings = {
                                            "марка меди": ["pa_marka_medi", "pa_steel", "pa_material"],
                                            "гост на прокат": ["pa_gost_na_prokat", "pa_gost", "pa_standard"],
                                            "толщина": ["pa_thickness", "pa_wall_thickness"],
                                            "диаметр": ["pa_diameter"],
                                            "сталь": ["pa_steel", "pa_material"]
                                        };
                                        
                                        for (const [russianName, englishSlugs] of Object.entries(attrMappings)) {
                                            if (text.includes(russianName) && englishSlugs.includes(attrSlug)) {
                                                const section = heading.closest(".product-card__section") || heading.parentElement;
                                                if (section) {
                                                    section.style.display = "";
                                                    section.style.visibility = "";
                                                    section.removeAttribute("data-smart-hidden");
                                                    console.log("Shown by text mapping:", russianName, "->", attrSlug, section);
                                                    found = true;
                                                }
                                            }
                                        }
                                    });
                                }
                                
                                if (!found) {
                                    console.log("No elements found for:", attrSlug);
                                }
                            });
                        }
                    }
                }
            }
            
            // Применяем сразу
            smartApplyVisibility();
            
            // Повторяем через интервалы
            setTimeout(smartApplyVisibility, 1000);
            setTimeout(smartApplyVisibility, 3000);
            
            // При изменении DOM
            const observer = new MutationObserver(() => {
                setTimeout(smartApplyVisibility, 100);
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true
            });
        })();
        </script>';
        
        // Добавляем стили для вариаций
        // Подключаем CSS файл для вариаций
        $css_url = get_template_directory_uri() . '/product-variations.css';
        echo '<link rel="stylesheet" href="' . esc_url($css_url) . '?v=' . time() . '">';
        
        // Добавляем JavaScript для вариаций
        echo '<script>
        (function() {
            console.log("=== PRODUCT VARIATIONS JS LOADED ===");
            
            function initVariations() {
                console.log("Initializing product variations...");
                
                const variationsContainer = document.querySelector(".product-variations");
                if (!variationsContainer) {
                    console.log("No variations container found");
                    return;
                }
                
                console.log("Found variations container:", variationsContainer);
                
                // Добавляем обработчики кликов
                const variationOptions = variationsContainer.querySelectorAll(".variation-option");
                variationOptions.forEach(option => {
                    option.addEventListener("click", function(e) {
                        e.preventDefault();
                        
                        const url = this.getAttribute("href");
                        console.log("Navigating to variation:", url);
                        
                        // Показываем индикатор загрузки
                        showLoadingIndicator();
                        
                        // Переходим на страницу вариации
                        window.location.href = url;
                    });
                });
                
                // Добавляем анимации
                addVariationAnimations();
            }
            
            function showLoadingIndicator() {
                const loadingDiv = document.createElement("div");
                loadingDiv.id = "variation-loading";
                loadingDiv.innerHTML = "<div class=\"loading-spinner\">Загрузка...</div>";
                loadingDiv.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 20px;
                    border-radius: 5px;
                    z-index: 9999;
                `;
                
                document.body.appendChild(loadingDiv);
                
                setTimeout(() => {
                    if (loadingDiv.parentNode) {
                        loadingDiv.parentNode.removeChild(loadingDiv);
                    }
                }, 3000);
            }
            
            function addVariationAnimations() {
                const variationsContainer = document.querySelector(".product-variations");
                if (!variationsContainer) return;
                
                // Анимация появления
                variationsContainer.style.opacity = "0";
                variationsContainer.style.transform = "translateY(20px)";
                variationsContainer.style.transition = "all 0.5s ease";
                
                setTimeout(() => {
                    variationsContainer.style.opacity = "1";
                    variationsContainer.style.transform = "translateY(0)";
                }, 100);
                
                // Анимация при наведении
                const options = variationsContainer.querySelectorAll(".variation-option");
                options.forEach(option => {
                    option.addEventListener("mouseenter", function() {
                        this.style.transform = "scale(1.05)";
                        this.style.boxShadow = "0 2px 8px rgba(0, 0, 0, 0.2)";
                    });
                    
                    option.addEventListener("mouseleave", function() {
                        this.style.transform = "scale(1)";
                        this.style.boxShadow = "none";
                    });
                });
            }
            
            // Инициализация
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(initVariations, 100);
                });
            } else {
                setTimeout(initVariations, 100);
            }
        })();
        </script>';
    }
}

// Построение групп зависимостей для фронтенда (для шаблона single)
function tema_souz_build_dependencies_groups($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) {
        error_log('DEBUG: No product found for ID: ' . $product_id);
        return []; 
    }

    // Конфиг зависимостей из админки (таблица wp_attribute_dependencies)
    $config = get_attribute_visibility_config();
    error_log('DEBUG: Config: ' . wp_json_encode($config));
    
    if (!$config || empty($config['rules'])) { 
        error_log('DEBUG: No config or rules found');
        return []; 
    }

    // Определяем тип товара по атрибутам, затем по заголовку (fallback)
    $type = get_product_type_from_attributes($product);
    error_log('DEBUG: Type from attributes: ' . ($type ?: 'null'));
    
    if (!$type) { 
        $type = get_product_type_from_title($product->get_name()); 
        error_log('DEBUG: Type from title: ' . ($type ?: 'null'));
    }
    
    if (!$type) { 
        error_log('DEBUG: No type found for product: ' . $product->get_name());
        return []; 
    }
    
    error_log('DEBUG: Final type: ' . $type);

    // Ищем правило для типа (нечувствительно к регистру + по slug)
    $rule = null;
    $type_lc  = mb_strtolower($type, 'UTF-8');
    $type_slug = sanitize_title($type);
    
    error_log('DEBUG: Looking for rule with type: ' . $type . ' (lc: ' . $type_lc . ', slug: ' . $type_slug . ')');
    
    foreach ($config['rules'] as $r) {
        if (empty($r['type'])) { continue; }
        $r_type     = (string) $r['type'];
        $r_type_lc  = mb_strtolower($r_type, 'UTF-8');
        $r_type_slug= sanitize_title($r_type);
        
        error_log('DEBUG: Checking rule type: ' . $r_type . ' (lc: ' . $r_type_lc . ', slug: ' . $r_type_slug . ')');
        
        if ($r_type === $type || $r_type_lc === $type_lc || $r_type_slug === $type_slug) { 
            $rule = $r; 
            error_log('DEBUG: Found matching rule: ' . wp_json_encode($rule));
            break; 
        }
    }
    
    if (!$rule || empty($rule['show'])) { 
        error_log('DEBUG: No matching rule found or rule has no show attributes');
        return []; 
    }

    $attributes_to_show = (array) $rule['show'];

    // Собираем товары того же типа (включая текущий для полноты значений)
    $same_type_ids = get_products_by_type($type, null);
    if (!in_array($product_id, $same_type_ids, true)) { $same_type_ids[] = $product_id; }

    // Для каждого атрибута собираем набор опций и первую подходящую ссылку
    $groups = [];
    
    // Отладочная информация
    error_log('DEBUG: Processing attributes: ' . wp_json_encode($attributes_to_show));
    
    foreach ($attributes_to_show as $taxonomy) {
        if (!$taxonomy) { continue; }
        
        error_log('DEBUG: Processing taxonomy: ' . $taxonomy);
        $options_map = [];
        $label = wc_attribute_label($taxonomy);

        foreach ($same_type_ids as $pid) {
            $p = wc_get_product($pid);
            if (!$p) { continue; }
            // Получаем значение атрибута у товара
            $attr_objects = $p->get_attributes();
            // Подбираем ключ атрибута с учётом возможных кодировок/вариантов
            $attr_key = tema_souz_match_attribute_key($attr_objects, $taxonomy);
            if (!$attr_key || !isset($attr_objects[$attr_key])) { continue; }
            $attr_obj = $attr_objects[$attr_key];
            if ($attr_obj->is_taxonomy()) {
                // Берём фактические термы у товара для отметки доступных значений
                $terms = wp_get_post_terms($pid, $attr_key, ['fields' => 'all']);
                if (!is_wp_error($terms) && !empty($terms)) {
                    foreach ($terms as $t) {
                        $slug = $t->slug;
                        if (!isset($options_map[$slug])) {
                            $options_map[$slug] = [
                                'name'      => $t->name,
                                'slug'      => $slug,
                                'url'       => get_permalink($pid),
                                'available' => true,
                            ];
                        }
                    }
                }
                // Также добавим ВСЕ термы таксономии (hide_empty=false) как недоступные (если не встретились у товаров того же типа)
                $all_terms = get_terms([
                    'taxonomy'   => $attr_key,
                    'hide_empty' => false,
                ]);
                if (!is_wp_error($all_terms) && !empty($all_terms)) {
                    foreach ($all_terms as $t) {
                        $slug = $t->slug;
                        if (!isset($options_map[$slug])) {
                            $options_map[$slug] = [
                                'name'      => $t->name,
                                'slug'      => $slug,
                                'url'       => '',
                                'available' => false,
                            ];
                        }
                    }
                }
            } else {
                $opts = (array) $attr_obj->get_options();
                if (!empty($opts)) {
                    $val = (string) $opts[0];
                    $slug = sanitize_title($val);
                    if (!isset($options_map[$slug])) {
                        $options_map[$slug] = [
                            'name'      => $val,
                            'slug'      => $slug,
                            'url'       => get_permalink($pid),
                            'available' => true,
                        ];
                    }
                }
            }
        }

        // Формируем группу, если нашли опции
        if (!empty($options_map)) {
            // Стабильный порядок: сначала по числовому значению, если распознаётся, иначе по имени
            $num = function($s) {
                if (preg_match('/([0-9]+(?:[\.,][0-9]+)?)/u', (string)$s, $m)) {
                    return floatval(str_replace(',', '.', $m[1]));
                }
                return null;
            };
            uasort($options_map, function($a, $b) use ($num){
                $na = $num($a['name']);
                $nb = $num($b['name']);
                if ($na !== null && $nb !== null) {
                    if ($na == $nb) return 0; return ($na < $nb) ? -1 : 1;
                }
                return strcasecmp($a['name'], $b['name']);
            });
            $groups[] = [
                'label'    => $label ?: $taxonomy,
                'attribute' => $taxonomy,
                'options'  => array_values($options_map),
            ];
            error_log('DEBUG: Added group for ' . $taxonomy . ' with ' . count($options_map) . ' options');
        } else {
            error_log('DEBUG: No options found for ' . $taxonomy);
        }
    }

    return $groups;
}

// Поиск подходящего ключа атрибута среди $attr_objects по желаемому $taxonomy,
// учитывая возможный urlencoding/decoding и sanitize_title
function tema_souz_match_attribute_key($attr_objects, $taxonomy) {
    if (isset($attr_objects[$taxonomy])) return $taxonomy;
    $candidates = [];
    $candidates[] = $taxonomy;
    $candidates[] = rawurldecode($taxonomy);
    $candidates[] = urlencode($taxonomy);
    $candidates[] = strtolower($taxonomy);
    $candidates[] = strtolower(rawurldecode($taxonomy));
    $candidates[] = strtolower(urlencode($taxonomy));
    // Попытка преобразовать хвост после 'pa_' с sanitize_title
    if (strpos($taxonomy, 'pa_') === 0) {
        $tail = substr($taxonomy, 3);
        $candidates[] = 'pa_' . sanitize_title($tail);
        $candidates[] = 'pa_' . sanitize_title(rawurldecode($tail));
    }
    foreach ($candidates as $key) {
        if ($key && isset($attr_objects[$key])) return $key;
    }
    // Последняя попытка: ищем по метке атрибута
    $target_label = wc_attribute_label($taxonomy);
    if ($target_label) {
        foreach ($attr_objects as $key => $obj) {
            if (wc_attribute_label($key) === $target_label) return $key;
        }
    }
    // Евристика: алиасы типа pa_tolsh (толщина), pa_shir (ширина) → ищем по части метки
    $alias = strtolower($taxonomy);
    $needle = '';
    if (strpos($alias, 'tolsh') !== false) { $needle = 'толщ'; }
    if (strpos($alias, 'shir') !== false)  { $needle = 'шир'; }
    if ($needle) {
        foreach ($attr_objects as $key => $obj) {
            $label = wc_attribute_label($key);
            if ($label && mb_stripos($label, $needle, 0, 'UTF-8') !== false) {
                return $key;
            }
        }
    }
    return null;
}

// Отдаём группы зависимостей для шаблона single через фильтр
add_filter('tema_souz/product_dependencies', function($groups, $productId){
    $built = tema_souz_build_dependencies_groups($productId);
    return (!empty($built)) ? $built : $groups;
}, 10, 2);

// Вспомогательная отладка типа и правил
add_action('wp_footer', function(){
    if (is_product() && isset($_GET['deps_type']) && $_GET['deps_type']=='1') {
        global $product; if (!$product) return;
        $cfg = get_attribute_visibility_config();
        $type_a = get_product_type_from_attributes($product);
        $type_t = get_product_type_from_title($product->get_name());
        echo '<!-- DEPS_TYPE name_attr=' . esc_html((string)$type_a) . ' name_title=' . esc_html((string)$type_t) . ' -->';
        $rule_types = [];
        if ($cfg && !empty($cfg['rules'])) { foreach ($cfg['rules'] as $r) { if (!empty($r['type'])) $rule_types[] = $r['type']; } }
        echo '<!-- DEPS_RULE_TYPES ' . wp_json_encode($rule_types, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . ' -->';
    }
}, 1000);

// AJAX обработчик для получения атрибутов товара
add_action('wp_ajax_get_product_attributes', 'handle_get_product_attributes');
add_action('wp_ajax_nopriv_get_product_attributes', 'handle_get_product_attributes');

function handle_get_product_attributes() {
    $product_id = intval($_POST['product_id']);
    $primary_attr = sanitize_text_field($_POST['primary_attr']);
    $secondary_attr = sanitize_text_field($_POST['secondary_attr']);
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product not found');
        return;
    }
    
    $attributes = $product->get_attributes();
    $primary_value = '';
    $secondary_value = '';
    
    // Получаем значение первичного атрибута
    if (isset($attributes[$primary_attr])) {
        $attr = $attributes[$primary_attr];
        if ($attr->is_taxonomy()) {
            $terms = wp_get_post_terms($product_id, $primary_attr);
            if (!empty($terms)) {
                $primary_value = $terms[0]->name;
            }
        } else {
            $options = $attr->get_options();
            if (!empty($options)) {
                $primary_value = $options[0];
            }
        }
    }
    
    // Получаем значение вторичного атрибута
    if (isset($attributes[$secondary_attr])) {
        $attr = $attributes[$secondary_attr];
        if ($attr->is_taxonomy()) {
            $terms = wp_get_post_terms($product_id, $secondary_attr);
            if (!empty($terms)) {
                $secondary_value = $terms[0]->name;
            }
        } else {
            $options = $attr->get_options();
            if (!empty($options)) {
                $secondary_value = $options[0];
            }
        }
    }
    
    wp_send_json_success([
        'primary' => $primary_value,
        'secondary' => $secondary_value,
        'url' => $product->get_permalink()
    ]);
}

// Встроенные стили для страницы товара (резервный вариант)
add_action('wp_head', function() {
    if (is_product() || (is_singular('product') && get_post_type() === 'product')) {
        ?>

        <?php
    }
});

// JavaScript для обработки вариаций товаров
add_action('wp_footer', function() {
    if (is_product()) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработка кликов по кнопкам вариаций
            const variationButtons = document.querySelectorAll('.thickness-option');
            
            // Отключено - используется более продвинутый обработчик в single-product.php
            /*
            variationButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Убираем активный класс с других кнопок в той же группе
                    const group = this.closest('.product-card__thickness-options');
                    if (group) {
                        const otherButtons = group.querySelectorAll('.thickness-option');
                        otherButtons.forEach(function(btn) {
                            btn.classList.remove('thickness-option--active');
                        });
                    }
                    
                    // Добавляем активный класс к текущей кнопке
                    this.classList.add('thickness-option--active');
                    
                    // Получаем данные о вариации
                    const attr = group ? group.getAttribute('data-attr-select') : null;
                    const value = this.getAttribute('data-value') || this.getAttribute('data-thickness');
                    
                    console.log('Variation selected:', {
                        attr: attr,
                        value: value
                    });
                    
                    // Если это атрибут из зависимостей, можно добавить логику поиска товаров
                    if (attr && value) {
                        // Здесь можно добавить AJAX запрос для поиска товаров с этим атрибутом
                        console.log('Looking for products with', attr, '=', value);
                    }
                });
            });
            */
            
            // Показываем информацию о загруженных вариациях
            console.log('Product variations loaded successfully');
        });
        </script>
        <?php
    }
});

// AJAX обработчик для поиска товара по атрибуту
add_action('wp_ajax_find_product_by_attribute', 'tema_souz_find_product_by_attribute');
add_action('wp_ajax_nopriv_find_product_by_attribute', 'tema_souz_find_product_by_attribute');

// AJAX обработчик для динамического заголовка товара
add_action('wp_ajax_get_dynamic_product_title', 'get_dynamic_product_title');
add_action('wp_ajax_nopriv_get_dynamic_product_title', 'get_dynamic_product_title');

function tema_souz_find_product_by_attribute() {
    error_log('=== DEBUG AJAX: Function called ===');
    error_log('DEBUG AJAX: POST data: ' . wp_json_encode($_POST));
    
    // Проверяем nonce для безопасности
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'calculation_request_nonce')) {
        error_log('DEBUG AJAX: Security check failed');
        wp_die('Security check failed');
    }
    
    $attribute = sanitize_text_field($_POST['attribute'] ?? '');
    $value = sanitize_text_field($_POST['value'] ?? '');
    $current_product_id = intval($_POST['current_product_id'] ?? 0);
    
    error_log('DEBUG AJAX: Request params - attribute: ' . $attribute . ', value: ' . $value . ', product_id: ' . $current_product_id);
    
    if (empty($attribute) || empty($value) || !$current_product_id) {
        error_log('DEBUG AJAX: Missing parameters');
        wp_send_json_error('Missing parameters');
        return;
    }
    
    // Получаем текущий товар для определения типа
    $current_product = wc_get_product($current_product_id);
    if (!$current_product) {
        error_log('DEBUG AJAX: Current product not found');
        wp_send_json_error('Current product not found');
        return;
    }
    
    // Определяем тип товара
    $type = get_product_type_from_attributes($current_product);
    if (!$type) { 
        $type = get_product_type_from_title($current_product->get_name()); 
    }
    
    error_log('DEBUG AJAX: Product type: ' . ($type ?: 'null'));
    
    if (!$type) {
        error_log('DEBUG AJAX: Cannot determine product type');
        wp_send_json_error('Cannot determine product type');
        return;
    }
    
    // Получаем все товары того же типа
    $same_type_ids = get_products_by_type($type, null);
    
    error_log('DEBUG AJAX: Same type products count: ' . count($same_type_ids));
    
    if (empty($same_type_ids)) {
        error_log('DEBUG AJAX: No products of same type found');
        wp_send_json_error('No products of same type found');
        return;
    }
    
    // Получаем все атрибуты текущего товара
    $current_attributes = $current_product->get_attributes();
    $tax_query = [];
    
    // Добавляем все атрибуты текущего товара, кроме изменяемого
    foreach ($current_attributes as $attr_name => $attr_obj) {
        if ($attr_name === $attribute) {
            // Пропускаем изменяемый атрибут
            continue;
        }
        
        if ($attr_obj->is_taxonomy()) {
            $terms = wp_get_post_terms($current_product_id, $attr_name, ['fields' => 'slugs']);
            if (!is_wp_error($terms) && !empty($terms)) {
                $tax_query[] = [
                    'taxonomy' => $attr_name,
                    'field' => 'slug',
                    'terms' => $terms,
                ];
            }
        }
    }
    
    // Добавляем новый атрибут
    $tax_query[] = [
        'taxonomy' => $attribute,
        'field' => 'slug',
        'terms' => $value,
    ];
    
    error_log('DEBUG AJAX: Tax query: ' . wp_json_encode($tax_query));
    
    // Ищем товар с указанным атрибутом среди товаров того же типа
    $query = new WP_Query([
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'post__in' => $same_type_ids,
        'tax_query' => $tax_query,
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);
    
    error_log('DEBUG AJAX: Query found ' . $query->found_posts . ' products');
    error_log('DEBUG AJAX: Query SQL: ' . $query->request);
    
    if ($query->have_posts()) {
        $product_id = $query->posts[0];
        $product_url = get_permalink($product_id);
        error_log('DEBUG AJAX: Found product ID: ' . $product_id . ', URL: ' . $product_url);
        wp_send_json_success(['product_url' => $product_url]);
    } else {
        error_log('DEBUG AJAX: No product found with attribute ' . $attribute . ' = ' . $value);
        error_log('DEBUG AJAX: Available products of same type: ' . wp_json_encode($same_type_ids));
        wp_send_json_error('Product with selected attribute not found');
    }
}

function get_dynamic_product_title() {
    error_log('DEBUG: get_dynamic_product_title called');
    error_log('DEBUG: POST data: ' . wp_json_encode($_POST));
    
    // Проверяем nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wc_ajax_nonce')) {
        error_log('DEBUG: Nonce verification failed');
        wp_die('Security check failed');
    }
    
    $product_id = intval($_POST['product_id']);
    $attributes = $_POST['attributes'];
    
    error_log('DEBUG: Product ID: ' . $product_id);
    error_log('DEBUG: Attributes: ' . wp_json_encode($attributes));
    
    if (!$product_id) {
        error_log('DEBUG: Invalid product ID');
        wp_send_json_error('Invalid product ID');
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product not found');
    }
    
    $original_title = $product->get_name();
    
    // Если нет выбранных атрибутов, возвращаем оригинальный заголовок
    if (empty($attributes)) {
        wp_send_json_success(['title' => $original_title]);
    }
    
    // Формируем дополнительную информацию для заголовка
    $additional_info = [];
    $processed_attrs = [];
    
    // Сначала обрабатываем толщину и ширину для формирования "толщина×ширина"
    $thickness_value = '';
    $width_value = '';
    
    foreach ($attributes as $attr_name => $attr_value) {
        if (empty($attr_value)) continue;
        
        // Получаем отображаемое значение
        $display_value = $attr_value;
        
        // Если это таксономический атрибут, получаем правильное название
        if (taxonomy_exists($attr_name)) {
            $term = get_term_by('slug', $attr_value, $attr_name);
            if ($term && !is_wp_error($term)) {
                $display_value = $term->name;
            } else {
                // Если не нашли по slug, пробуем декодировать URL
                $display_value = rawurldecode($attr_value);
            }
        } else {
            // Для не-таксономических атрибутов тоже пробуем декодировать
            $display_value = rawurldecode($attr_value);
        }
        
        // Сохраняем значения толщины и ширины
        if ($attr_name === 'pa_tolsh' || $attr_name === 'pa_thickness') {
            $thickness_value = $display_value;
            $processed_attrs[] = $attr_name;
        } elseif ($attr_name === 'pa_shir' || $attr_name === 'pa_width') {
            $width_value = $display_value;
            $processed_attrs[] = $attr_name;
        }
    }
    
    // Формируем "толщина×ширина" если есть оба значения
    if ($thickness_value && $width_value) {
        $additional_info[] = $thickness_value . '×' . $width_value;
    } elseif ($thickness_value) {
        $additional_info[] = $thickness_value;
    } elseif ($width_value) {
        $additional_info[] = $width_value;
    }
    
    // Обрабатываем остальные атрибуты
    foreach ($attributes as $attr_name => $attr_value) {
        if (empty($attr_value) || in_array($attr_name, $processed_attrs)) continue;
        
        // Получаем отображаемое значение
        $display_value = $attr_value;
        
        // Если это таксономический атрибут, получаем правильное название
        if (taxonomy_exists($attr_name)) {
            $term = get_term_by('slug', $attr_value, $attr_name);
            if ($term && !is_wp_error($term)) {
                $display_value = $term->name;
            } else {
                // Если не нашли по slug, пробуем декодировать URL
                $display_value = rawurldecode($attr_value);
            }
        } else {
            // Для не-таксономических атрибутов тоже пробуем декодировать
            $display_value = rawurldecode($attr_value);
        }
        
        $additional_info[] = $display_value;
    }
    
    // Формируем новый заголовок
    if (!empty($additional_info)) {
        $new_title = $original_title . ' ' . implode(' ', $additional_info);
    } else {
        $new_title = $original_title;
    }
    
    error_log('DEBUG: Original title: ' . $original_title);
    error_log('DEBUG: New title: ' . $new_title);
    error_log('DEBUG: Additional info: ' . wp_json_encode($additional_info));
    error_log('DEBUG: Raw attributes: ' . wp_json_encode($attributes));
    
    wp_send_json_success(['title' => $new_title]);
}

add_filter( 'get_terms', function( $terms, $taxonomies, $args ) {
  if ( is_admin() || ! in_array( 'product_cat', (array) $taxonomies, true ) ) {
    return $terms;
  }

  $hide = array( 1817 ); // ID категории, которую скрываем

  return array_values( array_filter( $terms, function( $term ) use ( $hide ) {
    return ! in_array( (int) $term->term_id, $hide, true );
  } ) );
}, 10, 3 );

// Подключаем динамические изображения по типу проката - УДАЛЕНО (дубликат)
// require_once get_stylesheet_directory() . '/dynamic-images-by-rental-type.php';


/**
 * Очистка кэша типов проката и ID товаров при изменении товаров
 * Это обеспечивает актуальность данных на главной странице, архиве категорий и в фильтрах
 */
function clear_rental_types_cache($post_id) {
    // Проверяем что это товар
    if (get_post_type($post_id) !== 'product') {
        return;
    }
    
    // Получаем все категории товара
    $categories = wp_get_post_terms($post_id, 'product_cat', ['fields' => 'ids']);
    
    if (!empty($categories) && !is_wp_error($categories)) {
        // Очищаем кэш для каждой категории
        foreach ($categories as $cat_id) {
            // Кэш типов проката (для главной страницы и архива категорий)
            delete_transient('rental_types_cat_' . $cat_id);
            
            // Кэш ID товаров (для фильтров в архиве)
            delete_transient('product_ids_cat_' . $cat_id);
        }
    }
}

// Хуки для очистки кэша
add_action('save_post_product', 'clear_rental_types_cache', 10, 1);
add_action('woocommerce_update_product', 'clear_rental_types_cache', 10, 1);
add_action('woocommerce_new_product', 'clear_rental_types_cache', 10, 1);
add_action('delete_post', 'clear_rental_types_cache', 10, 1);

// Очистка кэша при изменении терминов товара
add_action('set_object_terms', function($object_id, $terms, $tt_ids, $taxonomy) {
    if ($taxonomy === 'product_cat' || $taxonomy === 'pa_тип-проката') {
        clear_rental_types_cache($object_id);
    }
}, 10, 4);


/**
 * Добавляем кнопку очистки кэша типов проката в админку
 */
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_node([
        'id'    => 'clear_rental_cache',
        'title' => 'Очистить кэш типов проката',
        'href'  => wp_nonce_url(admin_url('admin-post.php?action=clear_rental_cache'), 'clear_rental_cache'),
    ]);
}, 100);

add_action('admin_post_clear_rental_cache', function() {
    if (!current_user_can('manage_options') || !check_admin_referer('clear_rental_cache')) {
        wp_die('Недостаточно прав');
    }
    
    global $wpdb;
    
    // Удаляем все transient'ы с типами проката и ID товаров
    $wpdb->query("
        DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_rental_types_cat_%' 
        OR option_name LIKE '_transient_timeout_rental_types_cat_%'
        OR option_name LIKE '_transient_product_ids_cat_%'
        OR option_name LIKE '_transient_timeout_product_ids_cat_%'
    ");
    
    wp_redirect(wp_get_referer() ?: admin_url());
    exit;
});



// Сброс permalinks при активации темы для корректной работы таксономий
add_action('after_switch_theme', 'tema_souz_flush_rewrite_rules');
function tema_souz_flush_rewrite_rules() {
    flush_rewrite_rules();
}


// ============================================================================
// ХЕЛПЕРЫ ДЛЯ КОНТАКТНЫХ ДАННЫХ
// ============================================================================

// Возвращает первый телефон или все телефоны из Carbon Fields
function ts_get_phones() {
    if (!function_exists('carbon_get_theme_option')) return [];
    $phones = carbon_get_theme_option('site_phones');
    if (empty($phones) || !is_array($phones)) return [];
    $result = [];
    foreach ($phones as $item) {
        $phone = is_array($item) ? ($item['phone'] ?? '') : (is_object($item) ? ($item->phone ?? '') : '');
        if ($phone) $result[] = $phone;
    }
    return $result;
}

function ts_get_phone($index = 0) {
    $phones = ts_get_phones();
    return $phones[$index] ?? '';
}

function ts_get_email() {
    if (!function_exists('carbon_get_theme_option')) return '';
    return (string) carbon_get_theme_option('site_email');
}

function ts_get_address() {
    if (!function_exists('carbon_get_theme_option')) return '';
    return (string) carbon_get_theme_option('site_address');
}

function ts_get_map() {
    if (!function_exists('carbon_get_theme_option')) return [];
    return [
        'lat'     => carbon_get_theme_option('contacts_map_lat')             ?: '60.0126',
        'lng'     => carbon_get_theme_option('contacts_map_lng')             ?: '30.3135',
        'zoom'    => carbon_get_theme_option('contacts_map_zoom')            ?: '15',
        'title'   => carbon_get_theme_option('contacts_map_balloon_title')   ?: 'СОЮЗ-МЕТАЛЛИСТ',
        'content' => carbon_get_theme_option('contacts_map_balloon_content') ?: ts_get_address(),
        'icon'    => (function() {
            $id = carbon_get_theme_option('contacts_map_icon');
            return $id ? wp_get_attachment_image_url($id, 'full') : '';
        })(),
    ];
}

// ============================================================================
// СЧЕТЧИК КОРЗИНЫ: КОЛИЧЕСТВО УНИКАЛЬНЫХ ПОЗИЦИЙ
// ============================================================================

// Изменение счетчика корзины: показывать количество уникальных позиций (строк), 
// а не общее количество товаров (сумму quantity)
// Это важно для металлопроката, где товары измеряются в погонных метрах, кг и т.д.
add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
    ob_start();
    ?>
    <span class="cart-badge"><?php echo WC()->cart ? count(WC()->cart->get_cart()) : 0; ?></span>
    <?php
    $fragments['.cart-badge'] = ob_get_clean();
    return $fragments;
}, 20);

// ============================================================================
// AJAX: ФОРМА ЗАЯВКИ С ГЛАВНОЙ СТРАНИЦЫ (HERO ORDER)
// ============================================================================
add_action('wp_ajax_hero_order_submit',        'tema_souz_hero_order_submit');
add_action('wp_ajax_nopriv_hero_order_submit', 'tema_souz_hero_order_submit');

function tema_souz_hero_order_submit() {
    if (!isset($_POST['hero_order_nonce_field']) || !wp_verify_nonce($_POST['hero_order_nonce_field'], 'hero_order_nonce')) {
        wp_send_json_error('Ошибка безопасности');
    }

    $name    = sanitize_text_field($_POST['hero_name']    ?? '');
    $phone   = sanitize_text_field($_POST['hero_phone']   ?? '');
    $inn     = sanitize_text_field($_POST['hero_inn']     ?? '');
    $comment = sanitize_textarea_field($_POST['hero_comment'] ?? '');

    if (empty($name) || empty($phone)) {
        wp_send_json_error('Пожалуйста, заполните обязательные поля.');
    }

    $to      = get_option('admin_email');
    $subject = 'Новая заявка с главной страницы — ' . $name;
    $body    = "<h2>Заявка с главной страницы</h2>
<p><strong>Имя:</strong> {$name}</p>
<p><strong>Телефон:</strong> {$phone}</p>
<p><strong>ИНН:</strong> " . ($inn ?: 'не указан') . "</p>
<p><strong>Комментарий:</strong> " . ($comment ?: 'не указан') . "</p>
<p><em>Отправлено: " . current_time('d.m.Y H:i') . "</em></p>";

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $attachments = [];

    // Обработка файла
    if (!empty($_FILES['hero_file']['name'])) {
        $allowed = ['application/pdf','application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (in_array($_FILES['hero_file']['type'], $allowed) && $_FILES['hero_file']['size'] <= 10485760) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $uploaded = wp_handle_upload($_FILES['hero_file'], ['test_form' => false]);
            if ($uploaded && empty($uploaded['error'])) {
                $attachments[] = get_attached_file(attachment_url_to_postid($uploaded['url'])) ?: $uploaded['file'];
            }
        }
    }

    $sent = wp_mail($to, $subject, $body, $headers, $attachments);
    if ($sent) {
        wp_send_json_success('Заявка отправлена');
    } else {
        wp_send_json_error('Ошибка при отправке письма');
    }
}
