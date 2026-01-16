<div class="pt-card" style="max-width: 800px;">
    <h2>📘 User Guide</h2>
    <p class="description">
        <strong>Plugin Tester & Activation Timer</strong> adds a safety layer to your WordPress workflow. 
        It prevents the dreaded "White Screen of Death" by testing plugins in a sandbox before activating them live.
    </p>

    <hr>

    <h3>🚦 How to Activate Plugins Safely</h3>
    
    <div style="background: #f0f6fc; border-left: 4px solid #72aee6; padding: 15px; margin: 20px 0;">
        <strong>Note:</strong> We have replaced the standard "Activate" link in your plugins list with <strong>Safely Activate</strong>.
    </div>

    <h4>Method 1: Safely Activate (Best for Permanent Plugins)</h4>
    <ol>
        <li>Go to your <strong>Plugins</strong> list.</li>
        <li>Click <strong>🛡️ Safely Activate</strong> next to any plugin.</li>
        <li>
            <strong>What happens next?</strong>
            <ul>
                <li>We send a "Scout" request to test the plugin in the background.</li>
                <li>If the plugin crashes, we catch the error file on the server.</li>
                <li>We block the activation and show you the exact error log.</li>
                <li>If the plugin is safe, it activates instantly.</li>
            </ul>
        </li>
    </ol>

    <h4>Method 2: Safely Timed Activate (Best for Debugging)</h4>
    <p>Use this for heavy tools like <em>Query Monitor</em>, <em>WP Migrate</em>, or <em>File Managers</em> that you don't want running forever.</p>
    <ol>
        <li>Click the red <strong>Safely Timed Activate</strong> link.</li>
        <li>Choose a duration (e.g., <strong>30 Mins</strong>).</li>
        <li>We verify safety first, then activate the plugin.</li>
        <li>The plugin will automatically deactivate when the timer runs out.</li>
    </ol>

    <hr>

    <h3>⚠️ Troubleshooting Crashes</h3>
    <p>If a plugin is blocked, go to the <strong>Error Logs</strong> tab to see why.</p>

    <table class="wp-list-table widefat striped" style="margin-top:10px;">
        <thead>
            <tr>
                <th>Error Type</th>
                <th>What it means</th>
                <th>How to fix</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Fatal Error</strong></td>
                <td>Code is broken (e.g., missing semicolon, undefined class).</td>
                <td>Contact the plugin developer with the file/line number from the log.</td>
            </tr>
            <tr>
                <td><strong>Memory Exhausted</strong></td>
                <td>The plugin needs more RAM than your server allows.</td>
                <td>Increase <code>memory_limit</code> in your <code>wp-config.php</code> file.</td>
            </tr>
            <tr>
                <td><strong>Timeout</strong></td>
                <td>The plugin took too long to load (Infinite loop or slow API).</td>
                <td>The plugin might be incompatible with your server speed.</td>
            </tr>
            <tr>
                <td><strong>Parse Error</strong></td>
                <td>Syntax error. The code cannot be read by PHP.</td>
                <td>The plugin file is likely corrupt. Re-download and upload it again.</td>
            </tr>
        </tbody>
    </table>

    <hr>

    <h3>⚙️ Frequently Asked Questions</h3>
    
    <h4>Why do I see "Internal Server Error" in the popup?</h4>
    <p>This means the crash was catastrophic (e.g., the server process died). However, our <strong>Dead Drop System</strong> usually captures the specific error details in the background. Check the <a href="?page=plugin-timer&tab=logs">Error Logs</a> tab for the full report.</p>

    <h4>Does the timer work if I close my browser?</h4>
    <p>Yes. The timer runs on the server (WP-Cron). You can log out or close the tab, and the plugin will still deactivate on schedule.</p>

    <h4>Where are the log files stored?</h4>
    <p>Temporary crash files are stored in <code>/wp-content/uploads/pt-logs/</code>. They are automatically deleted after being read to keep your server clean.</p>
</div>