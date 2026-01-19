=== Safe Activation & Timer ===
Contributors: prabin15
Tags: plugins, safety, crash protection, timer, sandbox, debugging
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 2.5.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Safely activate plugins with Pre-Flight Crash Checks (Fatal Error Protection) and set automatic deactivation timers.

== Description ==

**Stop breaking your site when activating plugins.**

Safe Activation & Timer replaces the standard "Activate" link with a smart **"Safely Activate"** workflow. Before a plugin is allowed to run on your live site, it passes through a sandboxed "Scout" process that checks for fatal errors, syntax crashes, and memory exhaustion.

**New in 2.5.0: Snapshot & Restore Engine**
We now capture a database snapshot before every activation. If a plugin crashes PHP completely (White Screen of Death), the system blindly restores the safe snapshot, guaranteeing your site stays online.

**Key Features:**

* **🛡️ Pre-Flight Checks:** Detects Fatal Errors, Parse Errors, and "Delayed Bombs" (crashes on `init`).
* **⏱️ Auto-Deactivation Timers:** Activate a plugin for 15 minutes, 1 hour, or 24 hours. It turns itself off automatically.
* **📸 Crash Protection:** Prevents WSOD (White Screen of Death) by reverting the database state immediately upon failure.
* **📝 Error Logging:** Captures the exact file, line number, and error message of the crash.
* **⚠️ Force Activation:** An "Activate Anyway" option for developers who need to debug the crash live.

**Perfect For:**

* Testing new/unstable plugins.
* Debugging live sites (enable a debug plugin for just 30 mins).
* Granting temporary feature access to clients.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/plugin-timer` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Go to the Plugins list; you will see "Safely Activate" links instead of the standard Activate links.

== Frequently Asked Questions ==

= Does this work with all plugins? =
Yes. It intercepts the standard WordPress activation process.

= What happens if a plugin crashes the checker? =
The plugin uses a "Dead Drop" system. If the checker dies, it writes the error to disk and forces a database rollback. You will see a "Activation Blocked" popup with the error details.

= Can I use this on a production site? =
Yes! That is exactly what it is designed for. It protects your production site from going down due to a bad plugin update or activation.

== Changelog ==

= 2.5.0 =
* **Major:** Implemented "Snapshot & Restore" engine for robust crash recovery.
* **Fix:** Added detection for "Delayed Bombs" (crashes on `init` hook).
* **Fix:** Added "Activate Anyway" option for developers.
* **Fix:** Compliance updates (Sanitization, Nonces, and `wp_delete_file`).
* **UI:** Improved Dashboard and Timer management.

= 2.0.0 =
* Initial Release of the Timer and Crash Check features.