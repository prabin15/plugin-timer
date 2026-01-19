# 🛡️ Safe Activation & Timer for WordPress

**Stop breaking your site when activating plugins.**

Safe Activation & Timer replaces the standard WordPress "Activate" link with a smart **"Safely Activate"** workflow. It runs a background "Scout" process to test plugins for fatal errors before they are allowed to load on your live site.



## 📥 Download

You can download the latest version directly from GitHub:

[**Download ZIP (v2.5.0)**](https://github.com/prabin15/plugin-timer/releases/download/v2.5.0/safe-activation.zip)

*Note: This version requires manual installation. Go to Plugins > Add New > Upload Plugin.*

---



## 🚀 Features

### 1. Crash Protection (The Scout)
Before a plugin activates, we run a sandboxed test that checks for:
* **Fatal Errors:** Class not found, undefined functions.
* **Parse Errors:** Syntax mistakes that usually break the whole site.
* **Memory Exhaustion:** Plugins that eat too much RAM.
* **Delayed Bombs:** Errors that only trigger on the `init` or `admin_init` hooks.

### 2. Snapshot & Restore Engine (New in v2.5.0)
If a plugin causes a hard crash (White Screen of Death), PHP stops working immediately. Our engine handles this by:
1.  Taking a **Snapshot** of active plugins before testing.
2.  Registering a **Shutdown Handler** that blindly restores this snapshot if the script dies.
3.  This guarantees your site comes back online instantly, even after a catastrophic failure.

### 3. Auto-Deactivation Timers
Activate a plugin temporarily. Great for debugging or client access.
* **Durations:** 15 mins, 30 mins, 1 hour, 12 hours, 24 hours.
* **Auto-Off:** The plugin automatically deactivates when the timer expires.

### 4. Developer Tools
* **Error Logs:** View detailed stack traces of blocked plugins.
* **Force Activation:** An "Activate Anyway" button to bypass safety checks if you *want* to break the site for debugging.

## 📦 Installation

1.  Download the `.zip` file.
2.  Upload to your WordPress site via **Plugins > Add New > Upload Plugin**.
3.  Activate "Safe Activation & Timer".
4.  Go to your **Plugins** list. You will see all "Activate" links have changed to **"Safely Activate"**.

## 🛠️ Usage

1.  Click **Safely Activate** on any plugin.
2.  Choose a duration (e.g., **30 Minutes** or **Forever**).
3.  Wait for the check (~2 seconds).
    * **If Safe:** The plugin activates and the page reloads.
    * **If Unsafe:** A red popup appears with the error details. The plugin remains inactive.

## 📋 Requirements
* WordPress 5.8+
* PHP 7.4+

## 📝 Changelog

**v2.5.0**
* **Major:** Implemented "Snapshot & Restore" database engine.
* **Feature:** Added "Activate Anyway" button.
* **Security:** Full compliance with WordPress.org security standards (Nonces, Sanitization).
* **Fix:** Improved handling of "Double Encoding" bugs in plugin paths.

**v2.0.0**
* Initial release with Timer and Basic Crash Checks.
