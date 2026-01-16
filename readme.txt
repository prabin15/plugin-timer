=== Safe Activation & Timer ===
Contributors: prabinregmi
Tags: debug, fatal error, crash protection, security, maintenance
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 2.5.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prevent the White Screen of Death. Test plugins for fatal errors before activating and set auto-deactivation timers.

== Description ==

**Activating a broken tool is the #1 cause of the "White Screen of Death."**

Whether it's a syntax error, memory exhaustion, or a PHP conflict, one bad click can take your site offline. **Safe Activation & Timer** adds a "Pre-Flight Check" to your workflow.

**🚀 FEATURES**

* **🛡️ Safely Activate (The Crash Guard)**
    We replace the standard activation link with a smart "Safely Activate" button. It launches a background "Scout" request to test the tool.
    * **If it crashes:** The activation is blocked instantly. Your live site stays online.
    * **If it's safe:** It activates immediately.

* **📝 Dead Drop Logging**
    Standard server logs often miss critical crashes. Our "Dead Drop" technology writes fatal errors directly to a temporary disk file the millisecond a crash occurs, ensuring you see the *exact* error message (File, Line, and Stack Trace) even if the server times out.

* **⏱️ Safely Timed Activate**
    Perfect for debugging. Activate heavy tools (like Query Monitor or Migrators) for **15 mins, 1 hour, or 4 hours**. The system automatically deactivates them when time is up, keeping your site fast.

* **⚠️ Crash Report Dashboard**
    View a history of blocked attempts and the specific errors they caused in **Tools > Safe Timer**.

== Installation ==

1.  Upload the folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin.
3.  Navigate to the **Plugins** list to see the new **Safely Activate** buttons.

== Frequently Asked Questions ==

= Does this work on LocalWP / XAMPP? =
Yes. We have optimized the crash detection to work on local environments where timeouts are frequent.

= Where are the crash logs stored? =
Temporary crash files are written to `/wp-content/uploads/pt-logs/`. They are automatically cleaned up after reading.

= Can I use the normal Activate button? =
The plugin "hijacks" the standard link for your safety. However, you can always use "Bulk Actions > Activate" to bypass our checks (not recommended).

== Screenshots ==

1.  **Safely Activate:** The new buttons added to your plugin list.
2.  **Crash Blocked:** The popup alert preventing a fatal error.
3.  **Error Logs:** The detailed crash report showing the exact file and line number.
4.  **Timer Selection:** Choosing a 30-minute duration for a debugging tool.

== Changelog ==

= 2.2.0 =
* Added "Dead Drop" logging system for hard crashes.
* Fixed Nginx 500 error masking.

= 2.1.0 =
* Added "Error Logs" tab.
* Renamed for clarity.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.2.0 =
Major Update: Added "Dead Drop" logging to capture fatal errors that previously caused empty log files.