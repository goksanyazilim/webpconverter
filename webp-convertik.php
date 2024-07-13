<?php
/*
Plugin Name: WebP Convertik for Media
Description: Converts uploaded images to WebP format and deletes the original files.
Version: 1.0
Author: Melikşah Gök
*/

function webp_converter_menu() {
    add_menu_page(
        'WebP Converter',
        'WebP Converter',
        'manage_options',
        'webp-converter',
        'webp_converter_page',
        'dashicons-admin-generic'
    );
}
add_action('admin_menu', 'webp_converter_menu');

function webp_converter_page() {
    $logs = get_option('webp_converter_logs', []);
    ?>
    <div class="wrap">
        <h1>WebP Converter Logs</h1>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) { ?>
                    <tr>
                        <td><?php echo esc_html($log['file']); ?></td>
                        <td><?php echo esc_html($log['status']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

function convert_to_webp($file) {
    $file_path = $file['file'];
    $file_type = $file['type'];

    // Desteklenen dosya türleri
    $supported_types = ['image/jpeg', 'image/png', 'image/tiff', 'image/gif', 'image/bmp'];

    if (in_array($file_type, $supported_types)) {
        $image = wp_get_image_editor($file_path);

        if (!is_wp_error($image)) {
            $destination = str_replace(pathinfo($file_path, PATHINFO_EXTENSION), 'webp', $file_path);

            $result = $image->save($destination, 'image/webp');

            if (!is_wp_error($result)) {
                // Orijinal dosyayı sil
                unlink($file_path);

                // Yeni dosya bilgilerini güncelle
                $file['file'] = $destination;
                $file['type'] = 'image/webp';
                $file['url'] = str_replace(pathinfo($file['url'], PATHINFO_EXTENSION), 'webp', $file['url']);

                // Başarılı dönüşüm log kaydı
                webp_converter_log($file_path, 'Success');
            } else {
                // Başarısız dönüşüm log kaydı
                webp_converter_log($file_path, 'Failed');
            }
        } else {
            // Başarısız dönüşüm log kaydı
            webp_converter_log($file_path, 'Failed');
        }
    }

    return $file;
}

function webp_converter_log($file, $status) {
    $logs = get_option('webp_converter_logs', []);
    $logs[] = ['file' => $file, 'status' => $status];
    update_option('webp_converter_logs', $logs);
}

add_filter('wp_handle_upload', 'convert_to_webp');

?>
