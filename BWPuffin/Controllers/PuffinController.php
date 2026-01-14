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

        add_filter( 'wp_mail_content_type','email_set_content_type' );

        add_filter( 'wp_mail', [$this, 'sendPuffinMail'], 10, 2);
        add_filter( 'pre_wp_mail', [$this, 'sendPuffinMailPre'], 10, 2);
    }

    public function email_set_content_type()
    {
        return "text/html";
    }

    public function sendPuffinMail($atts)
    {
        $headers = $atts['headers'] ?? '';
        $cc = '';
        $from = '';
        $fromName = '';

        if(!is_array($headers)){
            $headers = str_replace( "\r\n", "\n", $headers );
            $tempHeaders = explode( "\n", $headers );
            
            foreach($tempHeaders as $tempHeader){
                if($cc = $this->getCcs($tempHeader)){
                    break;
                }
            }

            foreach($tempHeaders as $tempHeader){
                if($from = $this->getFrom($tempHeader)){
                    break;
                }
            }

            foreach($tempHeaders as $tempHeader){
                if($fromName = $this->getFrom($tempHeader, 'name')){
                    break;
                }
            }
        } else {
            foreach($headers as $header){
                if($cc = $this->getCcs($header))
                    break;
            }
        }

        // $message = apply_filters('the_content', $atts['message']);
        $message = $atts['message'];
        $to = $atts['to'];
        $subject = $atts['subject'];
        $attachments = $atts['attachments'];
        $files = [];

        if($attachments){
            foreach($attachments as $attachment){
                $files[] = [
                    'filename'  => basename($attachment),
                    'contents'  => base64_encode(file_get_contents($attachment)),
                ];
            }
        }

        $puffin = $this->puffin->NewInstance();
        $puffin->to_recipients = $to;
        $puffin->cc_recipients = $cc;
        $puffin->bcc_recipients = null;
        $puffin->subject = $subject;
        $puffin->replyto = '';
        $puffin->body = $message; 
        $puffin->attachments = $files;
        $puffin->application_id = get_option('options_puffin_client_id');

        $puffin->from_email = $from;
        $puffin->from_name = $fromName;

        $result = $puffin->postEmail();

        return true;
    }

    public function sendPuffinMailPre($null, $atts)
    {
        return true;
    }

    public function getCcs($data)
    {
        $ccSpilt = explode("Cc:", $data);
        $CcCollection = [];
        if($ccSpilt[1] ?? false){
            $allccs = explode(",", $ccSpilt[1]);

            foreach($allccs as $singleCc){
                // new line cleanup
                $singleCc = str_replace( ['\r\n', '\n'], "", $singleCc );
                // inside tag striping
                preg_match('/<(.*?)>/', $singleCc, $cleanEmail);
                $found = $cleanEmail[1] ?? '';

                if($found){
                    $CcCollection[] = trim($found);
                } else {
                    $CcCollection[] = trim($singleCc);
                }
            }
        }

        $cc = implode(',', $CcCollection);
        return $cc;
    }

    public function getFrom($data, $result = 'email')
    {
        $split = explode("From:", $data);
        $name = '';
        $CcCollection = [];

        if($split[1] ?? false){
            $splitName = explode("<", $split[1]);
            $name = trim($splitName[0] ?? '');

            $parts = explode(",", $split[1]);

            foreach($parts as $single){
                // new line cleanup
                $single = str_replace( ['\r\n', '\n'], "", $single );
                // inside tag striping
                preg_match('/<(.*?)>/', $single, $cleanEmail);
                $found = $cleanEmail[1] ?? '';

                if($found){
                    $CcCollection[] = trim($found);
                } else {
                    $CcCollection[] = trim($single);
                }
            }
        }

        $from = implode(',', $CcCollection);

        if($result == 'email'){
            return $from;
        } else {
            return $name;
        }
    }
}