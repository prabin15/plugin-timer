<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="pt-card" style="max-width: 900px;">
    
    <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2 style="margin-top:0;">📘 User Guide & Documentation</h2>
        <p style="font-size: 16px; margin: 0; color: #50575e;">
            <strong>Safe Activation & Timer</strong> adds a crash-protection layer to your WordPress workflow. 
            It prevents the dreaded "White Screen of Death" by sandboxing plugins before they load on your live site.
        </p>
    </div>

    <hr style="border:0; border-top:1px solid #dcdcde; margin: 30px 0;">

    <h3>🛡️ The Safety Architecture</h3>
    <p>We use a 3-stage process to ensure your site never goes offline due to a bad plugin.</p>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px;">
        <div style="flex: 1; min-width: 250px; background: #f6f7f7; padding: 20px; border: 1px solid #dcdcde; border-radius: 4px;">
            <h4 style="margin-top:0;">1. The Scout 🕵️‍♀️</h4>
            <p>We send a background "Scout" request to activate the plugin in a sandbox. It triggers:</p>
            <ul style="list-style: disc; margin-left: 20px; font-size: 13px;">
                <li>Standard Activation Hooks</li>
                <li><code>init</code> hooks (to catch "Delayed Bombs")</li>
                <li><code>admin_init</code> hooks (to catch Admin UI crashes)</li>
            </ul>
        </div>

        <div style="flex: 1; min-width: 250px; background: #f6f7f7; padding: 20px; border: 1px solid #dcdcde; border-radius: 4px;">
            <h4 style="margin-top:0;">2. Snapshot & Restore 📸</h4>
            <p>Before testing, we take a <strong>Database Snapshot</strong> of your active plugins.</p>
            <p style="font-size: 13px;">If PHP crashes completely (e.g., White Screen of Death), our <strong>Emergency Shutdown Protocol</strong> blindly restores the safe snapshot, forcing the bad plugin off instantly.</p>
        </div>

        <div style="flex: 1; min-width: 250px; background: #f6f7f7; padding: 20px; border: 1px solid #dcdcde; border-radius: 4px;">
            <h4 style="margin-top:0;">3. The Timer ⏱️</h4>
            <p>If safe, you can choose how long the plugin stays active.</p>
            <ul style="list-style: disc; margin-left: 20px; font-size: 13px;">
                <li><strong>Forever:</strong> Standard activation.</li>
                <li><strong>Timed:</strong> (e.g., 30 mins). Great for debugging or giving clients temporary access.</li>
            </ul>
        </div>
    </div>

    <hr style="border:0; border-top:1px solid #dcdcde; margin: 30px 0;">

    <h3>🚦 How to Activate Plugins</h3>

    <div style="background: #fff; border: 1px solid #c3c4c7; padding: 0 20px 20px; margin-bottom: 20px;">
        <h4>Method 1: Safely Activate (Best for Permanent Plugins)</h4>
        <ol>
            <li>Go to your <strong>Plugins</strong> list.</li>
            <li>Click the blue <strong>🛡️ Safely Activate</strong> link next to any plugin.</li>
            <li>Select <strong>"Forever (Standard)"</strong> in the popup (or choose a time).</li>
            <li>
                <strong>What happens next?</strong>
                <ul>
                    <li>The system runs the Pre-Flight Check (~2 seconds).</li>
                    <li>If the plugin is broken, activation is <strong>Blocked</strong> and an error is shown.</li>
                    <li>If the plugin is safe, the page reloads with the plugin active.</li>
                </ul>
            </li>
        </ol>
    </div>

    <div style="background: #fff; border: 1px solid #c3c4c7; padding: 0 20px 20px;">
        <h4>Method 2: Timed Activation (Best for Debugging)</h4>
        <p>Use this for heavy tools like <em>Query Monitor</em>, <em>WP Migrate</em>, or <em>File Managers</em> that you don't want running forever.</p>
        <ol>
            <li>Click <strong>Safely Activate</strong>.</li>
            <li>Choose a duration (e.g., <strong>30 Mins</strong> or <strong>1 Hour</strong>).</li>
            <li>The plugin activates immediately (if safe).</li>
            <li>A countdown timer is saved in the database. When it reaches zero, the plugin deactivates automatically.</li>
        </ol>
    </div>

    <hr style="border:0; border-top:1px solid #dcdcde; margin: 30px 0;">

    <h3>⚠️ Troubleshooting Crashes</h3>
    <p>If a plugin is blocked, you will see a red popup. Here is how to interpret the errors:</p>

    <table class="wp-list-table widefat fixed striped" style="margin-top:10px;">
        <thead>
            <tr>
                <th style="width: 20%;">Error Type</th>
                <th>What it means</th>
                <th>How to fix</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Fatal Error</strong></td>
                <td>Code is broken (e.g., missing class, undefined function).</td>
                <td>Copy the file/line number from the log and report it to the developer.</td>
            </tr>
            <tr>
                <td><strong>Delayed Bomb</strong></td>
                <td>The plugin activates fine, but crashes immediately on the next page load (on the <code>init</code> hook).</td>
                <td>Our Scout caught this early. Do not activate this plugin.</td>
            </tr>
            <tr>
                <td><strong>Memory Exhausted</strong></td>
                <td>The plugin needs more RAM than your server allows.</td>
                <td>Increase <code>memory_limit</code> in your <code>wp-config.php</code> file.</td>
            </tr>
            <tr>
                <td><strong>Parse Error</strong></td>
                <td>Syntax error. PHP cannot even read the file.</td>
                <td>The plugin file is likely corrupt. Delete and re-upload it.</td>
            </tr>
            <tr>
                <td><strong>Silent Crash</strong></td>
                <td>The process died without an error message (e.g., a hidden <code>die()</code> or timeout).</td>
                <td>Check your server's <code>error_log</code> for details.</td>
            </tr>
        </tbody>
    </table>

    <hr style="border:0; border-top:1px solid #dcdcde; margin: 30px 0;">

    <h3>⚙️ Frequently Asked Questions</h3>
    
    <h4>Can I force a plugin to activate even if it errors?</h4>
    <p><strong>Yes.</strong> When an activation is blocked, the popup includes an <strong>"Activate Anyway"</strong> button. Use this with extreme caution—it bypasses our safety checks and may crash your site immediately.</p>

    <h4>Does the timer work if I close my browser?</h4>
    <p><strong>Yes.</strong> The timer is stored in the database and checked on every page load (via <code>init</code> hook). You can log out or close the tab, and the plugin will still deactivate on schedule.</p>

    <h4>Where are the crash logs stored?</h4>
    <p>Temporary crash files are stored in <code>/wp-content/uploads/pt-logs/</code>. They are automatically deleted immediately after being read to keep your server clean.</p>
    
    <h4>Why did "Activate Anyway" crash my site?</h4>
    <p>Because you asked it to! That button disables the "Snapshot & Restore" engine. If the plugin has a fatal error, WordPress will try to run it, resulting in a standard PHP crash.</p>

    <div style="margin-top: 40px; text-align: center;">
        <a href="?page=plugin-timer&tab=dashboard" class="button button-primary button-large">View Active Timers</a>
        <a href="?page=plugin-timer&tab=logs" class="button button-secondary button-large">View Error Logs</a>
    </div>
</div>