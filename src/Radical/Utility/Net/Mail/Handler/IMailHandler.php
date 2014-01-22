<?php
namespace Radical\Utility\Net\Mail\Handler;
use Radical\Utility\Net\Mail\Message;

interface IMailHandler {
	function send(Message $message,$body);
}