=== Plugin Tester and Activation Timer ===
Contributors: yourgithubusername
Tags: debug, safe mode, fatal error, crash protection, white screen of death, query monitor, auto deactivate, maintenance
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 2.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prevent the White Screen of Death. Automatically test plugins for fatal errors before activating them, and set timers to auto-deactivate debugging tools.

== Description ==

**Activating a broken plugin is the #1 cause of the WordPress "White Screen of Death."** Whether it's a syntax error, a memory limit exhaustion, or a PHP version conflict, one bad click can take your site offline. **Plugin Tester** solves this by adding a "Pre-Flight Check" to your workflow.

**🚀 FEATURES**

* **🛡️ Safely Activate (The Crash Guard)**
    We replace the standard activation link with a smart "Safely Activate" button. When clicked, it launches a background "Scout" request to test the plugin.
    * **If it crashes:** The plugin is blocked instantly. Your live site stays online.
    * **If it's safe:** It activates immediately.

* **📝 Dead Drop Logging (New in 2.2)**
    Standard server logs often miss critical crashes. Our "Dead Drop" technology writes fatal errors directly to a temporary disk file the millisecond a crash occurs, ensuring you see the *exact* error message (File, Line, and Stack Trace) even if the server times out.

* **⏱️ Safely Timed Activate**
    Perfect for developers. Activate heavy tools (like Query Monitor, WP Migrate, or Log Viewers) for **15 mins, 1 hour, or 4 hours**. The plugin automatically deactivates them when time is up, keeping your site fast.

* **⚠️ Crash Report Dashboard**
    View a history of blocked plugins and the specific errors they caused in **Tools > Plugin Timer > Error Logs**.

**Use Cases:**
1.  **Updating Legacy Sites:** Test old plugins without fear of breaking the site.
2.  **Client Handoff:** Ensure clients can't accidentally break their site by activating conflicting plugins.
3.  **Performance:** Prevent "forgotten" debugging plugins from slowing down your site.

== Installation ==

1.  Upload the `plugin-timer` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin.
3.  Navigate to the **Plugins** list. You will see the new **Safely Activate** buttons.

== Frequently Asked Questions ==

= Does this work on LocalWP / XAMPP? =
Yes. We have optimized the crash detection to work on local environments where timeouts are frequent.

= Where are the crash logs stored? =
Temporary crash files are written to `/wp-content/uploads/pt-logs/`. They are automatically cleaned up after reading.

= Can I use the normal Activate button? =
The plugin "hijacks" the standard Activate link for your safety. However, you can always use "Bulk Actions > Activate" to bypass our checks (not recommended).

== Screenshots ==

1.  **Safely Activate:** The new buttons added to your plugin list.
2.  **Crash Blocked:** The popup alert preventing a fatal error.
3.  **Error Logs:** The detailed crash report showing the exact file and line number.
4.  **Timer Selection:** Choosing a 30-minute duration for a debugging plugin.

== Changelog ==

= 2.2.0 =
* **Feature:** Added "Dead Drop" logging system to capture hard crashes that kill the PHP process.
* **Fix:** Resolved issue where "Internal Server Error" hid the real error message on Nginx servers.
* **Improvement:** Added "Client-Side Crash Reporter" as a backup for network timeouts.

= 2.1.0 =
* Renamed to "Plugin Tester and Activation Timer".
* Added "Error Logs" tab.

= 1.0.0 =
* Initial release.