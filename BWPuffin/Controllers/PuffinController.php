<?php
namespace BWPuffin\Controllers;

use BWPuffin\Puffin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PuffinController 
{
    protected $second_attempt;
    protected $puffin;

    public function __construct(Puffin $puffin)
    {
        $this->puffin = $puffin;

        add_filter( 'wp_mail', [$this, 'sendPuffinMail'], 10, 2);
        add_filter( 'pre_wp_mail', [$this, 'sendPuffinMailPre'], 10, 2);
    }

    public function sendPuffinMail($atts)
    {
        $headers = $atts['headers'];
        $cc = '';

        $cc_spilt = explode("Cc:", $headers);
        if($cc_spilt[1] ?? false){
            $cc = $cc_spilt[1];
        }

        $message = $atts['message'];
        $to = $atts['to'];
        $subject = $atts['subject'];
       
        $attachments = $atts['attachments'];
        
        $puffin = $this->puffin->NewInstance();
        $puffin->to_recipients = $to;
        $puffin->cc_recipients = $cc;
        $puffin->bcc_recipients = null;
        $puffin->subject = $subject;
        $puffin->replyto = '';
        $puffin->body = $message; 
        $puffin->attachments = $attachments;
        $puffin->application_id = get_option('options_puffin_client_id');

        $result = $puffin->postEmail();
  
        return true;
    }

    public function sendPuffinMailPre($null, $atts)
    {
        return true;
    }
}