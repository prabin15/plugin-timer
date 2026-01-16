<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PT_GitHub_Updater {

    private $slug;
    private $plugin_file;
    private $github_user;
    private $github_repo;

    public function __construct( $plugin_file, $github_user, $github_repo ) {
        $this->plugin_file = $plugin_file;
        $this->slug = dirname( plugin_basename( $plugin_file ) );
        $this->github_user = $github_user;
        $this->github_repo = $github_repo;

        // 1. Check for updates
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
        
        // 2. Show details in "View Version Details" popup
        add_filter( 'plugins_api', [ $this, 'check_info' ], 10, 3 );
        
        // 3. Fix the folder name after update (GitHub zips have version numbers in folders)
        add_filter( 'upgrader_source_selection', [ $this, 'fix_folder_name' ], 10, 4 );
    }

    // Check GitHub for latest release
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        // Get latest release from GitHub API
        $release = $this->get_github_release();
        
        if ( ! $release ) return $transient;

        // Get current version
        $current_version = get_plugin_data( $this->plugin_file )['Version'];
        
        // Compare (GitHub tag vs Local Version)
        // Note: Release tag must start with 'v' or be clean (e.g., "2.2.0" or "v2.2.0")
        $remote_version = ltrim( $release->tag_name, 'v' );

        if ( version_compare( $current_version, $remote_version, '<' ) ) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->plugin = plugin_basename( $this->plugin_file );
            $obj->new_version = $remote_version;
            $obj->url = $release->html_url;
            $obj->package = $release->zipball_url; // Auto-generated zip from GitHub
            
            $transient->response[ $obj->plugin ] = $obj;
        }

        return $transient;
    }

    // Popup Info
    public function check_info( $false, $action, $arg ) {
        if ( ! isset( $arg->slug ) || $arg->slug !== $this->slug ) return $false;

        $release = $this->get_github_release();
        if ( ! $release ) return $false;

        $obj = new stdClass();
        $obj->name = 'Plugin Tester & Timer';
        $obj->slug = $this->slug;
        $obj->version = ltrim( $release->tag_name, 'v' );
        $obj->author = '<a href="' . $release->html_url . '">' . $this->github_user . '</a>';
        $obj->homepage = $release->html_url;
        $obj->download_link = $release->zipball_url;
        $obj->sections = [
            'description' => $release->body // Uses the GitHub Release notes
        ];

        return $obj;
    }

    // API Helper
    private function get_github_release() {
        $cache_key = 'pt_gh_release_' . $this->slug;
        $release = get_transient( $cache_key );

        if ( false === $release ) {
            $url = "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest";
            $response = wp_remote_get( $url, [ 'headers' => [ 'User-Agent' => 'WP-Plugin-Updater' ] ] );

            if ( is_wp_error( $response ) ) return false;
            
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );

            if ( ! empty( $data->tag_name ) ) {
                $release = $data;
                set_transient( $cache_key, $release, 3600 ); // Cache for 1 hour
            }
        }
        return $release;
    }

    // Rename folder from "repo-name-v2.2.0" back to "plugin-timer"
    public function fix_folder_name( $source, $remote_source, $upgrader, $hook_extra ) {
        if ( isset( $hook_extra['plugin'] ) && strpos( $hook_extra['plugin'], $this->slug ) !== false ) {
            global $wp_filesystem;
            $new_source = trailingslashit( $remote_source ) . '../' . $this->slug . '/';
            $wp_filesystem->move( $source, $new_source );
            return $new_source;
        }
        return $source;
    }
}