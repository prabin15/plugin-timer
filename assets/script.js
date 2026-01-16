jQuery(document).ready(function($) {
    
    // 1. HIJACK DEFAULT ACTIVATE LINKS
    $('.plugins .activate a').each(function() {
        var $link = $(this);
        var url = $link.attr('href');
        
        $link.html('🛡️ Safely Activate');
        $link.css('font-weight', 'bold').css('color', '#135e96');

        $link.on('click', function(e) {
            e.preventDefault();
            var match = url.match(/plugin=([^&]+)/);
            if (!match) { window.location = url; return; }
            var pluginSlug = decodeURIComponent(match[1]);

            $link.text('Testing...').css('opacity', '0.6').css('pointer-events', 'none');

            // Send "0" minutes = Forever
            performActivation(pluginSlug, 0, $link, function() {
                $link.text('✓ Safe! Reloading...');
                window.location.reload();
            });
        });
    });

    // 2. POPUP LOGIC
    var popupHTML = `
        <div id="pt-overlay" style="display:none;">
            <div id="pt-modal">
                <div class="pt-icon">⏱️</div>
                <h3>Safely Timed Activate</h3>
                <p>We will test the plugin first. If safe, it stays on for:</p>
                <div class="pt-grid">
                    <button class="pt-btn" data-time="15">15 Mins</button>
                    <button class="pt-btn" data-time="30">30 Mins</button>
                    <button class="pt-btn" data-time="60">1 Hour</button>
                    <button class="pt-btn" data-time="240">4 Hours</button>
                    <button class="pt-btn" data-time="1440">24 Hours</button>
                </div>
                <button id="pt-cancel">Cancel</button>
            </div>
        </div>
    `;
    $('body').append(popupHTML);

    var selectedPlugin = '';

    $('.pt-trigger').on('click', function(e) {
        e.preventDefault();
        selectedPlugin = $(this).data('plugin');
        $('#pt-overlay').fadeIn(200);
    });

    $('.pt-btn').on('click', function() {
        var minutes = $(this).data('time');
        var $btn = $(this);
        $('.pt-btn').prop('disabled', true).css('opacity', '0.5');
        $btn.text('Testing...');

        performActivation(selectedPlugin, minutes, $btn, function() {
            window.location.reload();
        });
    });

    $('#pt-cancel').on('click', function() { $('#pt-overlay').fadeOut(); });

    // 3. SHARED ACTIVATION LOGIC (Trojan Horse Handler)
    function performActivation(plugin, minutes, $uiElement, successCallback) {
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
                // SUCCESS SCENARIO 1: Normal Activation
                if(response.success) {
                    successCallback();
                } 
                // FAILURE SCENARIO 1: Trojan Horse Crash (200 OK but contains crash data)
                else if (response.is_crash_report) {
                     console.log('Plugin Timer: Trojan Horse Crash Detected');
                     reportCrash(plugin, '200 (Trojan)', response.data, function() {
                        handleUIReset($uiElement);
                        showBlockedPopup();
                     });
                }
                // FAILURE SCENARIO 2: Standard PHP Logic Block
                else {
                    handleUIReset($uiElement);
                    showBlockedPopup();
                }
            },
            error: function(xhr, status, error) {
                // FAILURE SCENARIO 3: Hard Timeout or Network Error
                console.log('Plugin Timer: AJAX Died (Hard Fail).');
                
                var crashDetails = xhr.responseText || error || "Unknown Fatal Error";
                // Strip HTML tags if we got a raw page back
                var cleanText = crashDetails.replace(/<[^>]*>?/gm, '');
                if (cleanText.length > 800) cleanText = cleanText.substring(0, 800) + '...';

                reportCrash(plugin, xhr.status, cleanText, function() {
                    handleUIReset($uiElement);
                    showBlockedPopup();
                });
            }
        });
    }

    // NEW: Client-Side Crash Reporter
    function reportCrash(plugin, status, errorText, callback) {
        $.ajax({
            url: pt_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'pt_report_crash',
                plugin: plugin,
                status: status,
                error_text: errorText,
                nonce: pt_vars.nonce
            },
            complete: function() {
                // We don't care if this succeeds or fails, just run callback
                if (typeof callback === 'function') callback();
            }
        });
    }

    function handleUIReset($uiElement) {
        $('#pt-overlay').fadeOut();
        if($uiElement.hasClass('pt-btn')) {
            $('.pt-btn').prop('disabled', false).css('opacity', '1');
            $uiElement.text('Try Again');
        } else {
            $uiElement.text('🛡️ Safely Activate').css('opacity', '1').css('pointer-events', 'auto');
        }
    }

    function showBlockedPopup() {
        var errorHTML = `
            <div id="pt-error-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:100001; display:flex; justify-content:center; align-items:center;">
                <div style="background:#fff; padding:30px; border-radius:8px; width:500px; box-shadow:0 10px 25px rgba(0,0,0,0.5); text-align:center;">
                    <div style="font-size:50px; margin-bottom:20px;">🚫</div>
                    <h2 style="color:#d63638; margin-top:0;">Activation Blocked</h2>
                    <p><strong>Safe Mode</strong> prevented a critical crash.</p>
                    <p>Details saved to Error Log.</p>
                    <div style="margin-top:25px;">
                        <a href="${pt_vars.logs_url}" class="button button-primary button-large">View Error Logs</a>
                        <button id="pt-close-error" class="button button-secondary button-large" style="margin-left:10px;">Close</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(errorHTML);
        $('#pt-close-error').on('click', function() { $('#pt-error-overlay').remove(); });
    }

    // Dashboard Actions (Keep existing)
    $(document).on('click', '.pt-stop-timer', function(e) { e.preventDefault(); var p=$(this).data('plugin'), r=$(this).closest('tr'); if(!confirm('Remove timer?'))return; $.post(pt_vars.ajax_url, {action:'pt_stop_timer', plugin:p, nonce:pt_vars.nonce}, function(res){ if(res.success) r.fadeOut(300,function(){$(this).remove()}); }); });
    $(document).on('click', '.pt-deactivate-now', function(e) { e.preventDefault(); var p=$(this).data('plugin'), r=$(this).closest('tr'); $(this).text('...'); $.post(pt_vars.ajax_url, {action:'pt_deactivate_now', plugin:p, nonce:pt_vars.nonce}, function(res){ if(res.success) r.css('background','#ffcccc').fadeOut(500,function(){$(this).remove()}); }); });
});