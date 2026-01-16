# 🛡️ Safe Activation & Timer

![License](https://img.shields.io/badge/License-GPLv2-blue.svg) ![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg) ![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)

> **Status:** Pending Review on WordPress.org. You can download the manual version below.



## 📥 Download

You can download the latest version directly from GitHub:

[**Download ZIP (v2.2.0)**](https://github.com/prabin15/plugin-timer/releases/download/v2.2.0/Plugin-timer.zip)

*Note: This version requires manual installation. Go to Plugins > Add New > Upload Plugin.*

---



## 🚀 Key Features

### 1. The "Dead Drop" Safety System
Most safety plugins fail because when PHP crashes, the logging system crashes too. We solved this.
* **How it works:** We register a `shutdown_function` that detects fatal errors.
* **The Drop:** Before the process dies, we write the error details to a `.txt` file in `wp-content/uploads`.
* **The Retrieval:** The dashboard reads this file to display the error, bypassing server 500 pages completely.

### 2. Safely Timed Activate
Don't leave heavy debugging tools running on production.
* Activate for **15 Mins**, **1 Hour**, or **24 Hours**.
* Auto-deactivates via `WP-Cron`.

### 3. Error Log Dashboard
A persistent history of every crash prevented.
* View File, Line Number, and Error Message.
* Filter by plugin.
* One-click log clearing.

## 📦 Installation

1.  Download the `.zip` file.
2.  Go to **WordPress Dashboard > Plugins > Add New > Upload**.
3.  Activate.
4.  Go to your **Plugins List**. All "Activate" links are now protected.

## 🛠️ Testing the Crash Protection

We provide a **Crash Suite** (a set of dummy plugins) to verify the system works.
1.  Create a folder `wp-content/plugins/crash-test/`.
2.  Create a file `crash.php` with this code:
    ```php
    <?php
    /* Plugin Name: Crash Test */
    if ( defined('ABSPATH') ) {
        // Direct Fatal Error
        $x = new ThisClassDoesNotExist();
    }
    ```
3.  Go to your Plugins list and click **Safely Activate**.
4.  **Result:** You should see a "Blocked" popup and a log entry for "Class not found".

## 🔧 Technical Details

* **AJAX Handler:** `wp_ajax_pt_test_and_activate`
* **Storage:** * Logs: `wp_options` table (`pt_error_logs`).
    * Temp Files: `/wp-content/uploads/pt-logs/`.
* **Cleanup:** Temp files are deleted immediately after reading. Logs are capped at 20 entries.

## 🤝 Contributing

1.  Fork the repository.
2.  Create a feature branch (`git checkout -b feature/NewSafetyCheck`).
3.  Commit your changes.
4.  Push to the branch.
5.  Open a Pull Request.

## 📝 License

GPLv2 or later.