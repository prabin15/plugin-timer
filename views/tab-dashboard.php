<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) exit;

$timers = get_option( 'pt_active_timers', [] );
?>

<div class="pt-card">
    <h3>⏳ Active Plugin Timers</h3>
    
    <?php if ( empty( $timers ) ) : ?>
        <p>No active timers. Activate a plugin with a timer to see it here.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Plugin</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $timers as $plugin => $expiry ) : 
                    $minutes_left = ceil( ( $expiry - time() ) / 60 );
                    // Check if file exists to avoid warnings if plugin was deleted manually
                    if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
                        continue;
                    }
                    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
                    $name = $plugin_data['Name'];
                    
                    // We prepare the slug, but we will ESCAPE it down below in the HTML
                    $slug = urlencode( $plugin );
                ?>
                <tr>
                    <td><strong><?php echo esc_html( $name ); ?></strong></td>
                    <td>
                        <span class="pt-badge pt-active">Active</span>
                        Closing in <strong><?php echo intval( $minutes_left ); ?> mins</strong>
                    </td>
                    <td>
                        <button class="button pt-stop-timer" data-plugin="<?php echo esc_attr( $slug ); ?>">Stop Timer (Keep Active)</button>
                        <button class="button pt-deactivate-now" data-plugin="<?php echo esc_attr( $slug ); ?>">Deactivate Now</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>