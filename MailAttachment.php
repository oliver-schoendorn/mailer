<?php

namespace OS\Mail;


use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

class MailAttachment extends MailPart
{
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->mimePart->setDisposition(Mime::DISPOSITION_ATTACHMENT);
        $this->mimePart->setEncoding(Mime::ENCODING_BASE64);
        $this->mimePart->setFileName('FooBar.png');
    }

    /**
     * Returns the actual mime part.
     *
     * @return MimePart
     */
    public function reveal(): MimePart
    {
        return $this->mimePart;
    }

    /**
     * This way you can use the following in email templates:
     * <img src="<?= $mailAttachment; ?>" alt="this is an attachment object" />
     *
     * @return string
     */
    public function __toString()
    {
        return 'cid:' . $this->mimePart->getId();
    }
}
