# oliver-schoendorn/mailer

This is a little zendframework/zend-mail wrapper that will automagically construct proper multipart mime mails.

Please feel free to submit pull requests. 

## Installation
```bash
composer require os/mailer
```

## Usage example


### Create new Mail object and initialize mail meta data
```php
<?php

use OS\Mail\Mail;

$mail = (new Mail())
    ->setSubject('email subject')
    ->setSender('foo@bar.dev', 'Foo Bar')
    ->setFrom('foo@bar.dev', 'Foo Bar')
    ->setTo('receipt@bar.dev', 'Receipt name');
```

### Add inline attachments

This is optional, but if you are using inline attachments, you have to keep track of the MailAttachment object, in order
to reference it in the html mail body.

If an inline attachment is not being referenced, some mail clients will show the attachment or a preview below the mail,
which might break your carefully crafted mail layout. 

```php
<?php

use OS\Mail\MailAttachment;

$inlineImage = (new MailAttachment())
   ->setContent(fopen('path/to/file.jpg', 'r'))
   ->setMimeType('image/jpg');

$mail->addInlineAttachment($inlineImage);
```

### Add body parts
```php
<?php

use Zend\Mime\Mime;
use OS\Mail\MailBody;

$mail->addBodyPart((new MailBody())
    ->setMimeType(Mime::TYPE_TEXT)
    ->setContent('Plain text mail content'));

$mail->addBodyPart((new MailBody())
    ->setMimeType(Mime::TYPE_HTML)
    ->setContent('<html><body><p>Html mail content<img src="' . $inlineImage . '" /></p></body></html>'));
```

### Add (non-inline) attachments

Usually, these only appear attachment in a context menu or similar, based on the mail client used. 

```php
<?php
use OS\Mail\MailAttachment;

$mail->addAttachment((new MailAttachment())
    ->setContent(fopen('path/to/file.jpg', 'r'))
    ->setMimeType('image/jpg'));
```

### Sending the mail

To send an mail, you can either define the transport object directly using the Zend classes or you can use the built
in MailTransportFactory, which provides some type hinting. 

```php
<?php
use OS\Mail\MailTransportFactory;

$mailTransport = (new MailTransportFactory())->createSmtpTransportWithLogin(
    $config['mail']['user'],
    $config['mail']['pass'],
    $config['mail']['encryption'],
    $config['mail']['name'],
    $config['mail']['host'],
    $config['mail']['port']
);

$mail->send($mailTransport);
```

## License

Copyright 2017 Oliver Schöndorn

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
