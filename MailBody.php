<?php

namespace OS\Mail;


use Zend\Mime\Mime;

class MailBody extends MailPart
{
    public function __construct(string $mimeType = 'text/html', bool $isAlternative = false)
    {
        parent::__construct();
        $this->mimePart->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
    }
}
