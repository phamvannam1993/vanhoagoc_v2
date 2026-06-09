<?php

namespace App\Jobs;

use App\Helpers\SendMailHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $subject;
    protected $body;
    protected $from;
    protected $fromName;

    public function __construct($to, $subject, $body, $from = null, $fromName = null)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->from = $from;
        $this->fromName = $fromName;
    }

    public function handle()
    {
        // Gọi lại helper gốc để gửi mail ngay trong job
        SendMailHelper::sendNow($this->to, $this->subject, $this->body, $this->from, $this->fromName);
    }
}
