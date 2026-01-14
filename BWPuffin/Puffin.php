<?php
namespace BWPuffin;

use BWPuffin\Traits\Bridge;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Puffin 
{
    use Bridge;

    public function NewInstance()
    {
        return new self();
    }

    public function __set($property, $value) {

        if($property == 'body'){
            $this->$property = $value;
        } else {
            $this->$property = sanitize_text_field($value);
        }
    }

    public function __get($name) {
        return $this->$name;
    }

    private function log($data)
    {
        //not yet done
    }

    function postEmail()
    {
        $fields = [
            'to_recipients',
            'cc_recipients',
            'bcc_recipients',
            'subject',
            'replyto',
            'body',
            'attachments',
        ];

        $processed_data = [];
        foreach($fields as $field){

            switch($field)
            {
                case 'to_recipients':
                case 'cc_recipients':
                case 'bcc_recipients':
                    if($this->$field){
                        $processed_data[$field] = explode(',', $this->$field);
                    }
                    break;
                case 'body':
                    $processed_data[$field] = $this->$field;
                    break;
                default:
                    $processed_data[$field] = $this->$field;
                    break;
            }
        }

        $processed_data['application_id'] = get_option('options_puffin_application_id');

        $this->log($processed_data);

        $result =  $this->initRequest([
            'url'   => trailingslashit(get_option('options_puffin_app_url')) . 'emails',
            'type'  => 'POST', 
            'body'  => $processed_data
        ]); 

        return $result;
    }
}