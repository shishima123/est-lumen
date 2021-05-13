<?php
namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\JWTAuth;
 
class MailVerify extends Mailable {
 
    use Queueable,
        SerializesModels;
    protected $code;
    
    public function __construct($_code)
    {
        $this->code = $_code;
    }
    //build the message.
    public function build() {
        $value= $this->code;
        return $this->view('email',compact('value'));
    }
}