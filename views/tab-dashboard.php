<?php
$timers = get_option( 'pt_active_timers', [] );
?>
<div class="pt-card">
    <?php if ( empty( $timers ) ) : ?>
        <p>No active timers. Go to the <a href="plugins.php">Plugins</a> page to start one.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Plugin</th><th>Status</th><th>Time Remaining</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $timers as $plugin_slug => $expiry ) : 
                    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_slug );
                    $name = $plugin_data['Name'] ? $plugin_data['Name'] : $plugin_slug;
                    $minutes_left = round( ($expiry - time()) / 60 );
                    $slug_attr = urlencode($plugin_slug);
                ?>
                <tr>
                    <td><strong><?php echo esc_html( $name ); ?></strong></td>
                    <td><span class="pt-badge-active">Active</span></td>
                    <td><?php echo $minutes_left > 0 ? "Closing in <strong>$minutes_left mins</strong>" : "Closing soon..."; ?></td>
                    <td>
                        <button class="button pt-stop-timer" data-plugin="<?php echo $slug_attr; ?>">🚫 Remove Timer</button>
                        <button class="button button-link-delete pt-deactivate-now" data-plugin="<?php echo $slug_attr; ?>">Deactivate Now</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>