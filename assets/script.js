jQuery(document).ready(function($) {

    // 1. SETUP POPUP HTML
    var popupHTML = `
        <div id="pt-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; justify-content:center; align-items:center;">
            <div style="background:#fff; padding:30px; border-radius:8px; width:400px; text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.3);">
                <div style="font-size:40px; margin-bottom:10px;">⏱️</div>
                <h2 style="margin-top:0;">Safely Timed Activate</h2>
                <p>We will test the plugin first. If safe, how long should it stay active?</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:20px 0;">
                    <button class="button pt-time-btn" data-time="15">15 Minutes</button>
                    <button class="button pt-time-btn" data-time="30">30 Minutes</button>
                    <button class="button pt-time-btn" data-time="60">1 Hour</button>
                    <button class="button pt-time-btn" data-time="120">2 Hours</button>
                    <button class="button pt-time-btn" data-time="720">12 Hours</button>
                    <button class="button pt-time-btn" data-time="0" style="background:#f0f0f1; border-color:#dcdcde;">Forever (Standard)</button>
                </div>
                <button id="pt-cancel" class="button-link" style="color:#d63638;">Cancel</button>
            </div>
        </div>
    `;
    $('body').append(popupHTML);

    // 2. HIJACK STANDARD ACTIVATION LINKS
    $('.plugins .activate a').each(function() {
        var $link = $(this);
        var url = $link.attr('href');
        
        // Check if it's a real activation link
        if (url && url.indexOf('action=activate') !== -1) {
            $link.html('🛡️ Safely Activate');
            $link.css({ 'font-weight': 'bold', 'color': '#2271b1' });
            
            $link.on('click', function(e) {
                e.preventDefault();
                var match = url.match(/plugin=([^&]+)/);
                var plugin = match ? decodeURIComponent(match[1]) : null;

                if(plugin) {
                    // Pass the ORIGINAL URL to the popup data
                    $('#pt-overlay').css('display', 'flex')
                        .data('plugin', plugin)
                        .data('ui', $link)
                        .data('original-url', url);
                } else {
                    window.location = url; 
                }
            });
        }
    });

    // 3. POPUP BUTTON CLICKS
    $('.pt-time-btn').on('click', function() {
        var minutes = $(this).data('time');
        var plugin = $('#pt-overlay').data('plugin');
        var $ui = $('#pt-overlay').data('ui');
        var originalUrl = $('#pt-overlay').data('original-url');

        $('#pt-overlay').hide();
        
        $ui.text('Checking...').css('opacity', '0.5').css('pointer-events', 'none');
        $('body').css('cursor', 'wait');

        performActivation(plugin, minutes, $ui, originalUrl, false);
    });

    $('#pt-cancel').on('click', function() { $('#pt-overlay').hide(); });

    // 4. MAIN ACTIVATION LOGIC
    function performActivation(plugin, minutes, $uiElement, originalUrl, force) {
        $.ajax({
            url: pt_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'pt_test_and_activate',
                plugin: plugin,
                minutes: minutes,
                force: force ? 'true' : 'false',
                nonce: pt_vars.nonce
            },
            success: function(response) {
                $('body').css('cursor', 'default');
                
                if (response.success) {
                    if($uiElement) $uiElement.text('Safe! Reloading...');
                    window.location.reload(); 
                } else {
                    // SAFETY CHECK FAILED
                    if($uiElement) $uiElement.text('🛡️ Safely Activate').css('opacity', '1').css('pointer-events', 'auto');
                    
                    var errorDetails = response.data || "Unknown Error";
                    showCrashPopup(plugin, errorDetails, originalUrl, minutes);
                }
            },
            error: function(xhr, status, error) {
                // If we were FORCING it, and it failed, it means the plugin crashed PHP.
                // We should reload to show the result (WSOD or Active)
                if (force) {
                    window.location.reload();
                    return;
                }

                // Otherwise, show the error popup
                $('body').css('cursor', 'default');
                if($uiElement) $uiElement.text('🛡️ Safely Activate').css('opacity', '1').css('pointer-events', 'auto');
                showCrashPopup(plugin, "Critical Server Error (500). PHP crashed.", originalUrl, minutes);
            }
        });
    }

    // 5. CRASH POPUP WITH "ACTIVATE ANYWAY"
    function showCrashPopup(plugin, error, originalUrl, minutes) {
        $('#pt-crash-modal').remove();

        var html = `
            <div id="pt-crash-modal" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:100000; display:flex; justify-content:center; align-items:center;">
                <div style="background:#fff; padding:30px; width:600px; max-width:90%; border-radius:5px; border-left:5px solid #d63638; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                    <h2 style="color:#d63638; margin-top:0;">🚫 Activation Blocked</h2>
                    <p><strong>Safe Mode</strong> prevented a fatal error on your site.</p>
                    
                    <div style="background:#f6f7f7; padding:15px; margin:15px 0; max-height:200px; overflow:auto; font-family:monospace; font-size:12px; border:1px solid #ddd;">
                        ${error.replace(/\n/g, '<br>')}
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                        <div>
                            <a href="${pt_vars.logs_url}" class="button button-secondary">View Error Logs</a>
                        </div>
                        <div>
                            <button id="pt-force-activate" class="button button-link-delete" style="color:#d63638; margin-right:10px; text-decoration:none;">Activate Anyway</button>
                            <button class="button button-primary" onclick="document.getElementById('pt-crash-modal').remove()">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(html);

        // HANDLE "ACTIVATE ANYWAY" CLICK
        $('#pt-force-activate').on('click', function(e) {
            e.preventDefault();
            
            if(!confirm('⚠️ Warning: This may crash your site.\n\nAre you sure you want to proceed?')) return;

            var $btn = $(this);
            $btn.text('Activating...');
            
            if (originalUrl) {
                // SCENARIO 1: Standard Link -> Redirect Browser (Bypass our Logic)
                window.location.href = originalUrl;
            } else {
                // SCENARIO 2: Timed Link -> Force AJAX
                // If this crashes, the error handler above will reload the page
                performActivation(plugin, minutes, null, null, true);
            }
        });
    }

    // 6. DASHBOARD ACTIONS
    $(document).on('click', '.pt-stop-timer, .pt-deactivate-now', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var action = $btn.hasClass('pt-stop-timer') ? 'pt_stop_timer' : 'pt_deactivate_now';
        var plugin = $btn.data('plugin');

        $btn.prop('disabled', true).text('Processing...');

        $.post(pt_vars.ajax_url, {
            action: action,
            plugin: plugin,
            nonce: pt_vars.nonce
        }, function(response) {
            if(response.success) {
                window.location.reload();
            } else {
                alert('Action failed.');
                $btn.prop('disabled', false).text('Try Again');
            }
        });
    });

});