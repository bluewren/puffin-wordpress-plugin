<?php
namespace BWPuffin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this , 'registerAdminPage'], 99);

        if($_POST['action'] ?? false){
            switch($_POST['action'])
            {
                case 'puffin_update_creds':
                    $this->postPuffinCredentials($_POST);
                    break;
            }
        }
    }

    public function registerAdminPage()
    {
        add_menu_page(
			'Puffin',
			'Puffin',
			'manage_options',
			'puffin_admin',
			[$this , 'renderAdminPage'],
			'dashicons-schedule',
			1
		);
    }

    public function renderAdminPage()
    {
        $token_url = get_option('options_puffin_token_url');
        $client_id = get_option('options_puffin_client_id');
        $app_url = get_option('options_puffin_app_url');
        $client_secret = get_option('options_puffin_client_secret');
        $application_id = get_option('options_puffin_application_id');
        $client_token = get_option('options_puffin_client_token');

        echo '<div class="wrap">';
        echo '<h2 class="" style="margin-bottom:10px;">BW Puffin</h2>';

        echo '<div id="acf-group_" class="postbox  acf-postbox">';
            echo '<div class="inside acf-fields -top" >';

            echo '<form action="" method="post"><p>Puffin Credentials</p>';
                echo '<div style="margin-bottom:10px">';  
                    echo '<input type="text" name="token_url" value="'.$token_url.'" placeholder="Token URL" style="min-width:300px;"/>'; 
                echo '</div>';
                echo '<div style="margin-bottom:10px">';  
                    echo '<input type="text" name="app_url" value="'.$app_url.'" placeholder="App URL" style="min-width:300px;"/>'; 
                echo '</div>';
                echo '<div style="margin-bottom:10px">';  
                    echo '<input type="text" name="client_secret" value="'.$client_secret.'" placeholder="Client Secret" style="min-width:300px;"/>'; 
                echo '</div>';
                echo '<div style="margin-bottom:10px">';  
                    echo '<input type="text" name="client_id" value="'.$client_id.'" placeholder="Client ID" style="min-width:300px;"/>'; 
                echo '</div>';
                echo '<div style="margin-bottom:10px">';  
                    echo '<input type="text" name="client_token" value="'.$client_token.'" placeholder="Token" style="min-width:300px;"/>'; 
                echo '</div>';
                echo '<div style="margin-bottom:10px">';  
                    echo '<input type="text" name="application_id" value="'.$application_id.'" placeholder="Application ID" style="min-width:300px;"/>'; 
                echo '</div>';
                echo '<input type="hidden" name="action" value="puffin_update_creds" />';
                echo '<input type="submit" name="update" value="Update" />';
            echo '</form>';

            echo '</div>';
        echo '</div>';
    }

    public function postPuffinCredentials($post)
    {
        if($post['action'] != 'puffin_update_creds') return false;

        $token_url = $post['token_url'] ?? '';
        $app_url = $post['app_url'] ?? '';
        $client_secret = $post['client_secret'] ?? '';
        $client_id = $post['client_id'] ?? '';
        $client_token = $post['client_token'] ?? '';
        $application_id = $post['application_id'] ?? '';

        update_option('options_puffin_token_url', $token_url);
        update_option('options_puffin_client_id', $client_id);
        update_option('options_puffin_client_secret', $client_secret);
        update_option('options_puffin_app_url', $app_url);
        update_option('options_puffin_client_token', $client_token);
        update_option('options_puffin_application_id', $application_id);
    }
}