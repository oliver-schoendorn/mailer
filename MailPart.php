<?php

namespace OS\Mail;


use Zend\Mime\Part as MimePart;

class MailPart
{
    /**
     * @var MimePart
     */
    protected $mimePart;

    /**
     * MailAttachment constructor.
     *
     * @param string $id
     */
    public function __construct(string $id = '')
    {
        // Generate a random id
        if ( ! $id) {
            $id = bin2hex(openssl_random_pseudo_bytes(8));
        }

        $this->mimePart = (new MimePart())->setId(trim($id, '<>') . '@mailer.oswebstyle.de');
    }

    /**
     * @param string|resource $content  String or Stream containing the content
     * @throws \InvalidArgumentException
     * @return static
     */
    public function setContent($content): MailPart
    {
        $this->mimePart->setContent($content);
        return $this;
    }

    /**
     * @param string $mimeType
     * @return static
     */
    public function setMimeType(string $mimeType): MailPart
    {
        $this->mimePart->setType($mimeType);
        return $this;
    }

    /**
     * @return MimePart
     */
    public function reveal(): MimePart
    {
        return $this->mimePart;
    }
}
