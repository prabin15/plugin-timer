<?php
$logs = get_option( 'pt_error_logs', [] );
$is_cleared = isset( $_GET['cleared'] );
?>

<div class="pt-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3>⚠️ Crash Reports</h3>
        <?php if ( !empty( $logs ) ) : ?>
        <form method="post" action="">
             <button type="submit" name="pt_clear_logs" value="1" class="button button-secondary">Clear Logs</button>
        </form>
        <?php endif; ?>
    </div>

    <?php if ( $is_cleared ) : ?>
        <div class="notice notice-success inline"><p>Logs cleared successfully.</p></div>
    <?php endif; ?>

    <?php if ( empty( $logs ) ) : ?>
        <div class="notice notice-info inline" style="padding: 20px; display:block;">
            <p style="font-size: 16px; margin:0;"><strong>✅ All Good!</strong></p>
            <p style="margin-top:5px;">No crashes detected recently.</p>
        </div>
    <?php else : ?>
        
        <?php foreach ( $logs as $log ) : 
            $plugin_slug = $log['plugin'];
            $plugin_name = $plugin_slug; 
            if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {
                $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_slug );
                $plugin_name = $plugin_data['Name'];
            }
            $time_string = date( 'M j, Y - g:i a', $log['time'] );
        ?>
        
        <div style="background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #d63638; border-radius: 4px; margin-bottom: 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="padding: 10px 15px; background: #f6f7f7; border-bottom: 1px solid #ccd0d4; display:flex; justify-content:space-between;">
                <strong>🚨 <?php echo esc_html( $plugin_name ); ?></strong>
                <span style="color: #646970; font-size: 12px;"><?php echo $time_string; ?></span>
            </div>
            <div style="padding: 15px;">
                <p style="margin-top:0; font-weight:600; color:#1d2327;">Error Output:</p>
                <div style="background: #2c3338; color: #f0f0f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.5;">
                    <?php echo esc_html( $log['error'] ); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>