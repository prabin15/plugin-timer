jQuery(document).ready(function($) {

    // --- 1. VISUALS: Rename "Activate" to "Safely Activate" ---
    $('.plugins .activate a').each(function() {
        var $link = $(this);
        // Only hijack if it's a real activation link
        if ($link.attr('href') && $link.attr('href').indexOf('action=activate') !== -1) {
            $link.html('🛡️ Safely Activate');
            $link.css('font-weight', 'bold').css('color', '#135e96');
        }
    });

    // --- 2. INTERCEPT STANDARD ACTIVATE CLICKS ---
    // We target the .activate class specifically
    $(document).on('click', '.plugins .activate a', function(e) {
        var $link = $(this);
        var url = $link.attr('href');

        // Verify it's an activation link
        if (!url || url.indexOf('action=activate') === -1) return;

        e.preventDefault();

        // Extract Plugin Slug from URL
        var match = url.match(/plugin=([^&]+)/);
        var plugin = match ? decodeURIComponent(match[1]) : null;

        if (!plugin) {
            window.location = url; // Fallback to normal if we can't find slug
            return;
        }

        // Show Loading State
        $link.text('Checking...').css('opacity', '0.6').css('pointer-events', 'none');

        // Run Safety Check (0 minutes = Forever)
        performActivation(plugin, 0, $link, function() {
            $link.text('✓ Safe! Activating...');
            window.location = url; // Proceed with standard WP activation
        });
    });

    // --- 3. HANDLE "TIMED ACTIVATE" BUTTONS ---
    $(document).on('click', '.pt-trigger', function(e) {
        e.preventDefault();
        var plugin = $(this).data('plugin');
        var $row = $(this).closest('tr');

        // Simple Prompt for Duration
        var minutes = prompt("⏱️ Safe Activation Timer\n\nHow many minutes should this plugin stay active?\n(Enter 0 for permanent activation)", "30");
        
        if (minutes === null) return; // Cancelled
        minutes = parseInt(minutes);
        if (isNaN(minutes)) minutes = 30;

        performActivation(plugin, minutes, $row, function() {
             window.location.reload(); // Reload to show active state
        });
    });

    // --- 4. MASTER ACTIVATION LOGIC ---
    function performActivation(plugin, minutes, $uiElement, successCallback) {
        // Global Cursor Wait
        $('body').css('cursor', 'wait');

        $.ajax({
            url: pt_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'pt_test_and_activate',
                plugin: plugin,
                minutes: minutes,
                nonce: pt_vars.nonce
            },
            success: function(response) {
                $('body').css('cursor', 'default');
                
                // RESTORE UI
                if($uiElement.hasClass('pt-trigger')) {
                    // It was a row action link
                    $uiElement.css('opacity', '1');
                } else {
                    // It was the main activate button
                     $uiElement.css('opacity', '1').css('pointer-events', 'auto');
                }

                if (response.success) {
                    // SAFE!
                    successCallback();
                } else {
                    // CRASH DETECTED!
                    // Reset text if it was the main button
                    if (!$uiElement.hasClass('pt-trigger')) {
                         $uiElement.html('🛡️ Safely Activate');
                    }
                    
                    var errorMsg = response.data || "Unknown Error";
                    reportCrash(plugin, errorMsg);
                }
            },
            error: function(xhr, status, error) {
                // HARD SERVER FAILURE (500)
                $('body').css('cursor', 'default');
                if (!$uiElement.hasClass('pt-trigger')) {
                     $uiElement.html('🛡️ Safely Activate').css('opacity', '1').css('pointer-events', 'auto');
                }

                var msg = "Critical Server Failure (500).";
                if(xhr.responseText) msg = xhr.responseText.substring(0, 500);
                
                reportCrash(plugin, msg);
            }
        });
    }

    // --- 5. DASHBOARD BUTTONS (FIXED) ---
    // Bug Fix: We now reload the page on success to prevent the row from "reappearing"
    
    // Stop Timer
    $(document).on('click', '.pt-stop-timer', function(e) {
        e.preventDefault();
        var btn = $(this);
        var plugin = btn.data('plugin');
        
        if(!confirm('Stop the timer? The plugin will remain active.')) return;
        
        btn.text('Processing...').prop('disabled', true);
        
        $.post(pt_vars.ajax_url, {
            action: 'pt_stop_timer',
            plugin: plugin,
            nonce: pt_vars.nonce
        }, function(res) {
            window.location.reload();
        });
    });

    // Deactivate Now
    $(document).on('click', '.pt-deactivate-now', function(e) {
        e.preventDefault();
        var btn = $(this);
        var plugin = btn.data('plugin');

        if(!confirm('Deactivate this plugin immediately?')) return;
        
        btn.text('Processing...').prop('disabled', true);
        
        $.post(pt_vars.ajax_url, {
            action: 'pt_deactivate_now',
            plugin: plugin,
            nonce: pt_vars.nonce
        }, function(res) {
            window.location.reload();
        });
    });

    // --- 6. CRASH POPUP ---
    function reportCrash(plugin, details) {
        // Remove existing if any
        $('#pt-crash-modal').remove();

        var html = `
            <div id="pt-crash-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:99999; display:flex; justify-content:center; align-items:center;">
                <div style="background:#fff; width:90%; max-width:600px; padding:30px; border-radius:8px; box-shadow:0 0 20px rgba(0,0,0,0.5); font-family:sans-serif; text-align:left;">
                    <h2 style="color:#d63638; margin-top:0;">🚫 Activation Blocked</h2>
                    <p><strong>Safe Mode</strong> prevented a fatal crash on your site.</p>
                    <div style="background:#f6f7f7; padding:15px; border-left:4px solid #d63638; margin:20px 0; overflow:auto; max-height:200px; font-family:monospace; font-size:12px;">
                        ${details.replace(/\n/g, '<br>')}
                    </div>
                    <div style="text-align:right;">
                        <a href="${pt_vars.logs_url}" class="button button-secondary">View Full Logs</a>
                        <button class="button button-primary button-large" onclick="document.getElementById('pt-crash-modal').remove()">Acknowledge & Close</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(html);
    }

});