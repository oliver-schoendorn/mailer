<?php
/**
 * Copyright 2018 Oliver Schöndorn
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OS\Mail\Mail;
use OS\Mail\MailAttachment;
use OS\Mail\MailBody;
use OS\Mail\MailTransportFactory;
use Zend\Mime\Mime;

require_once __DIR__ . '/../../vendor/autoload.php';

// Read config
$config = (function(string $configPath): array {
    if ( ! file_exists($configPath)) die($configPath . ' does not exist');
    return parse_ini_file($configPath);
})(__DIR__ . '/config.ini');

// Basic mail instance construction
$newMail = function (string $subject, callable $fn) use($config)
{
    echo PHP_EOL . 'Sending "' . $subject . '" ...';
    $fn((new Mail())
        ->setSubject($subject)
        ->setFrom($config['MAIL_FROM'])
        ->setTo($config['MAIL_TO'])
    );
    echo "\r" . 'Completed sending "' . $subject . '"';
};

function openFile(string $filePath, string $mode = 'r')
{
    return fopen(__DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $filePath), $mode);
}

// Create a transport to send the mails with
$transport = (new MailTransportFactory())->createSmtpTransportWithLogin(
    $config['USER_NAME'],
    $config['USER_PASS'],
    $config['SERVER_ENCR'],
    $config['SERVER_NAME'],
    $config['SERVER_HOST'],
    $config['SERVER_PORT']
);

// Prepare mail parts
$plainBody = new MailBody(Mime::TYPE_TEXT);
$plainBody->setContent('Hello world, this is a plain text mail part. äöüß');

$htmlBody = new MailBody(Mime::TYPE_HTML);
$htmlBody->setContent('<html><body><h1>Hello world</h1><p>This is an html äöüß &amp; mail part</p></body></html>');

$attachmentPart = new MailAttachment('image.png', 'image/png');
$attachmentPart->setContent(openFile('/images/attachment.png'));

$inlineAttachment = new MailAttachment('inline.png', 'image/png');
$inlineAttachment->setContent(openFile('/images/inline.png'));

// Plain only
$newMail('Test mail (plain)', function(Mail $mail) use($plainBody, $transport) {
    $mail->addBodyPart($plainBody);
    $mail->send($transport);
});

// Plain + Html
$newMail('Test mail (plain + html)', function(Mail $mail) use($plainBody, $htmlBody, $transport) {
    $mail->addBodyPart($plainBody);
    $mail->addBodyPart($htmlBody);
    $mail->send($transport);
});

// Plain + Html + Attachment
$newMail('Test mail (plain + html + attachment)', function(Mail $mail) use($plainBody, $htmlBody, $attachmentPart, $transport) {
    $mail->addBodyPart($plainBody);
    $mail->addBodyPart($htmlBody);
    $mail->addAttachment($attachmentPart);
    $mail->send($transport);
});

// Plain + Html + Attachment + Inline-Attachment
$newMail('Test mail (plain + html + attachment + inline)', function(Mail $mail) use($plainBody, $htmlBody, $inlineAttachment, $attachmentPart, $transport) {
    $htmlBody->setContent('<html><body><h1>Hello world</h1><p>This is...</p><img src="' . $inlineAttachment . '" /><p>...an html äöüß &amp; mail part</p></body></html>');
    $mail->addBodyPart($plainBody);
    $mail->addBodyPart($htmlBody);
    $mail->addAttachment($attachmentPart);
    $mail->addInlineAttachment($inlineAttachment);
    $mail->send($transport);
});

//echo $transport->getLastMessage()->toString();

echo PHP_EOL . 'Completed test' . PHP_EOL;
