<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{

    public function __construct()
    {
    }

    /**
     * @param string $type i.e. HTML or TEXT
     * @param string $fromEmail i.e. test@email.com
     * @param string $senderName i.e. ['foo@example.com', 'foo123@example.com']
     * @param string $sub i.e. 'The mail subject'
     * @param array  $to i.e. ['foo@example.com', 'foo123@example.com']
     * @param array  $cc i.e. ['foo@example.com', 'foo123@example.com']
     * @param array  $bcc i.e. ['foo@example.com', 'foo123@example.com']
     * @param string $body i.e. 'Hi, welcome user!';
     * @param array $files i.e. ['https://file-examples.com/wp-content/uploads/2017/02/file-sample_100kB.doc',
     *                     'https://file-examples.com/wp-content/uploads/2017/02/file-sample_100kB.doc']
     * @param array $filesData i.e. ['foo@example.com', 'foo123@example.com']
     *
     * @return array|bool
     */
    public function sendEmail($type = 'TEXT', $fromEmail = '', $senderName = '', $sub = '', $to = [], $cc = [], $bcc = [], $body = '', $files = [], $filesData=[])
    {
        if ($type == 'HTML') {
            Mail::send([], [], function ($message) use ($fromEmail, $senderName, $sub, $to, $cc, $bcc, $body, $files, $filesData) {
                $message->from($fromEmail, $senderName);
                $message->subject($sub)->setBody($body, 'text/html');
                $message->to($to);
                if ($cc) {
                    $message->cc($cc);
                }
                if ($bcc) {
                    $message->bcc($bcc);
                }
                if ($files) {
                    foreach ($files as $file) {
                        $message->attach($file);
                    }
                }
                if ($filesData) {
                    foreach ($filesData as $file) {
                        $message->attachData(
                            $file['file_data'],
                            $file['file_name'],
                            ['mime' =>$file['mime'],
                            ]);
                    }
                }
            });
            if (Mail:: failures()) {
//                Log::error(Mail:: failures());
//            return Mail:: failures();
                return false;
            } else {
                return true;
            }

        } elseif ($type == 'TEXT') {
            Mail::raw($body, function ($message) use ($fromEmail, $senderName, $sub, $to, $cc, $bcc, $files, $filesData) {
                $message->subject($sub);
                $message->from($fromEmail, $senderName);
                $message->to($to);
                if ($cc) {
                    $message->cc($cc);
                }
                if ($bcc) {
                    $message->bcc($bcc);
                }
                if ($files) {
                    foreach ($files as $file) {
                        $message->attach($file);
                    }
                }
                if ($filesData) {
                    foreach ($filesData as $file) {
                        $message->attachData(
                            $file['file_data'],
                            $file['file_name'],
                            ['mime' =>$file['mime'],
                            ]);
                    }
                }
            });

            if (Mail:: failures()) {
//            return Mail:: failures();
                return false;
            } else {
                return true;
            }

        }else{
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            $headers .= 'From: '.$fromEmail."\r\n".
                'Reply-To: '.$fromEmail."\r\n" .
                'X-Mailer: PHP/' . phpversion();

            if (mail($to, $sub, $body, $headers)) {
                return false;
            } else {
                return true;
            }
        }

    }
}
