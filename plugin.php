<?php
/**
 * Plugin Name: Posts Google Translation (v.2)
 * Description: A plugin to google translate the posts that load for the first time.
 * Version: 1.0
 * Author: Svjatoslav Kachmar
 */

// Add a menu for option page
add_action('admin_menu', 'post_transl_plugin_add_settings_menu');
function post_transl_plugin_add_settings_menu()
{
    add_options_page('POSTTRANSL Plugin Settings', 'Post Google Translation Settings', 'manage_options', 'post_transl_plugin', 'post_transl_plugin_option_page');
}

// Create the option page
function post_transl_plugin_option_page()
{
?>
    <div class="wrap">
    <h2>Posts Translation Plugin</h2>
    <form action="options.php" method="post">
    <?php
    settings_fields('post_transl_plugin_options');
    do_settings_sections('post_transl_plugin');
    submit_button('Save Changes', 'primary');
?>
    </form>
    </div>
    <?php
}
// Register and define the settings
add_action('admin_init', 'post_transl_plugin_admin_init');
function post_transl_plugin_admin_init()
{
    // Define the setting args
    $args = array(
        'type' => 'string',
        'sanitize_callback' => 'post_transl_plugin_validate_options',
        'default' => NULL
    );
    // Register settings
    register_setting('post_transl_plugin_options', 'post_transl_plugin_options', $args);

    // Add a settings section
    add_settings_section('post_transl_plugin_main', 'POSRTTRANSL Plugin Settings', 'post_transl_plugin_section_text', 'post_transl_plugin');

    // Create settings field for from language
    add_settings_field('post_transl_plugin_from_lang', 'From', 'post_transl_setting_from_lang', 'post_transl_plugin', 'post_transl_plugin_main');

    // Create our settings field for to language
    add_settings_field('post_transl_plugin_to_lang', 'To', 'post_transl_setting_to_lang', 'post_transl_plugin', 'post_transl_plugin_main');
}

// Draw the section header
function post_transl_plugin_section_text()
{
    echo '<p>Enter your settings here.</p>';
}

// Display and select the from language select field
function post_transl_setting_from_lang()
{
    // Get option value from the database
    // Set to 'en' as a default if the option does not exist
    $options = get_option('post_transl_plugin_options', ['from' => 'en']);
    $from = $options['from'];
    // Define the select option values for from
    $items = array(
        'af',
        'sq',
        'ar',
        'az',
        'eu',
        'bn',
        'be',
        'bg',
        'ca',
        'zh-CN',
        'zh-TW',
        'hr',
        'cs',
        'da',
        'nl',
        'en',
        'eo',
        'et',
        'tl',
        'fi',
        'fr',
        'gl',
        'ka',
        'de',
        'el',
        'gu',
        'ht',
        'iw',
        'hi',
        'hu',
        'is',
        'id',
        'ga',
        'it',
        'ja',
        'kn',
        'ko',
        'la',
        'lv',
        'lt',
        'mk',
        'ms',
        'mt',
        'no',
        'fa',
        'pl',
        'pt',
        'ro',
        'ru',
        'sr',
        'sk',
        'sl',
        'es',
        'sw',
        'sv',
        'ta',
        'te',
        'th',
        'tr',
        'uk',
        'ur',
        'vi',
        'cy',
        'yi'
    );
    echo "<select id='post_transl_from' name='post_transl_plugin_options[from]'>";
    foreach ($items as $item)
    {
        // Loop through the option values
        // If saved option matches the option value, select it
        echo "<option value='" . esc_attr($item) . "'
 " . selected($from, $item, false) . ">" . esc_html($item) . "</option>";
    }
    echo "</select>";
}

// Display and set to language select field
function post_transl_setting_to_lang()
{
    // Get option value from the database
    // Set to 'en' as a default if the option does not exist
    $options = get_option('post_transl_plugin_options', ['to' => 'en']);
    $to = $options['to'];
    // Define the select option values for to language
    $items = array(
        'af',
        'sq',
        'ar',
        'az',
        'eu',
        'bn',
        'be',
        'bg',
        'ca',
        'zh-CN',
        'zh-TW',
        'hr',
        'cs',
        'da',
        'nl',
        'en',
        'eo',
        'et',
        'tl',
        'fi',
        'fr',
        'gl',
        'ka',
        'de',
        'el',
        'gu',
        'ht',
        'iw',
        'hi',
        'hu',
        'is',
        'id',
        'ga',
        'it',
        'ja',
        'kn',
        'ko',
        'la',
        'lv',
        'lt',
        'mk',
        'ms',
        'mt',
        'no',
        'fa',
        'pl',
        'pt',
        'ro',
        'ru',
        'sr',
        'sk',
        'sl',
        'es',
        'sw',
        'sv',
        'ta',
        'te',
        'th',
        'tr',
        'uk',
        'ur',
        'vi',
        'cy',
        'yi'
    );
    echo "<select id='to' name='post_transl_plugin_options[to]'>";
    foreach ($items as $item)
    {
        // Loop through the option values
        // If saved option matches the option value, select it
        echo "<option value='" . esc_attr($item) . "'
 " . selected($to, $item, false) . ">" . esc_html($item) . "</option>";
    }
    echo "</select>";
}

// Validate user input for all three options
function post_transl_plugin_validate_options($input)
{
    // Sanitize the data we are receiving
    $valid['to'] = sanitize_text_field($input['to']);
    $valid['from'] = sanitize_text_field($input['from']);
    return $valid;
}

// Translate when the post is loaded
add_action('wp', 'my_translate');
function my_translate()
{
    if (is_singular())
    {
        global $post;
        $translated = get_post_meta($post->ID);
        if (!isset($translated['translated']))
        {
            $options = get_option('post_transl_plugin_options');
            $to = $options['to'];
            $from = $options['from'];
            $apiKey = '<YOUR-GOOGLE-CLOUD-TRANSLATION-API-KEY>';
            $title_of_post = $post->post_title;
            $content_of_post = $post->post_content;
            $url = 'https://www.googleapis.com/language/translate/v2?key=' . $apiKey . '&q=' . rawurlencode($title_of_post) . '&q=' . rawurlencode($content_of_post) . '&source=' . $from . '&target=' . $to;
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($handle);
            $responseDecoded = json_decode($response, true);
            $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE); //Here we fetch the HTTP response code
            curl_close($handle);
            if ($responseCode != 200)
            {
                echo 'Fetching translation failed! Server response code:' . $responseCode . '<br>';
                echo 'Error description: ' . $responseDecoded['error']['errors'][0]['message'];
            }
            else
            {
                $my_post = array(
                    'ID' => $post->ID,
                    'post_title' => $responseDecoded['data']['translations'][0]['translatedText'],
                    'post_content' => $responseDecoded['data']['translations'][1]['translatedText'],
                );

                add_post_meta($post->ID, 'translated', 'translated');

                // Update the post into the database
                wp_update_post($my_post);
                header('Location: ' . $_SERVER['REQUEST_URI']);
            }
        }
    }
}

