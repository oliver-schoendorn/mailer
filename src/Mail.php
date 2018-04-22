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

namespace OS\Mail;

use Zend\Mail\AddressList;
use Zend\Mail\Message as MailMessage;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

class Mail
{
    /**
     * @var MailMessage
     */
    protected $mail;

    /**
     * @var MailPart[]
     */
    protected $bodyParts = [];

    /**
     * @var MailPart[]
     */
    protected $inlineAttachments = [];

    /**
     * @var MailPart[]
     */
    protected $attachments = [];

    /**
     * Mail constructor.
     *
     * @param string $encoding
     */
    public function __construct(string $encoding = 'UTF-8')
    {
        $this->mail = (new MailMessage())
            ->setEncoding($encoding);
    }

    /**
     * @param string $subject
     *
     * @return Mail
     */
    public function setSubject(string $subject): Mail
    {
        $this->mail->setSubject($subject);
        return $this;
    }

    /**
     * @param AddressList $addressList
     *
     * @return Mail
     */
    public function setFromList(AddressList $addressList): Mail
    {
        $this->mail->setFrom($addressList);
        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return Mail
     */
    public function setFrom(string $address, string $name = ''): Mail
    {
        $this->mail->setFrom($address, $name);
        return $this;
    }

    /**
     * @param AddressList $addressList
     *
     * @return Mail
     */
    public function setReplyToList(AddressList $addressList): Mail
    {
        $this->mail->setReplyTo($addressList);
        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return Mail
     */
    public function setReplyTo(string $address, string $name): Mail
    {
        $this->mail->setReplyTo($address, $name);
        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return Mail
     */
    public function setSender(string $address, string $name): Mail
    {
        $this->mail->setSender($address, $name);
        return $this;
    }

    /**
     * @param AddressList $addressList
     *
     * @return Mail
     */
    public function setToList(AddressList $addressList): Mail
    {
        $this->mail->setTo($addressList);
        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return Mail
     */
    public function setTo(string $address, string $name = ''): Mail
    {
        $this->mail->setTo($address, $name);
        return $this;
    }

    /**
     * @param AddressList $addressList
     *
     * @return Mail
     */
    public function setCcList(AddressList $addressList): Mail
    {
        $this->mail->setCc($addressList);
        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return Mail
     */
    public function setCc(string $address, string $name = ''): Mail
    {
        $this->mail->setCc($address, $name);
        return $this;
    }

    /**
     * @param AddressList $addressList
     *
     * @return Mail
     */
    public function setBccList(AddressList $addressList): Mail
    {
        $this->mail->setBcc($addressList);
        return $this;
    }

    /**
     * @param string $address
     * @param string $name
     *
     * @return Mail
     */
    public function setBcc(string $address, string $name): Mail
    {
        $this->mail->setBcc($address, $name);
        return $this;
    }

    /**
     * Adds a body part to the mail message.
     *
     * @param MailPart $part
     *
     * @return Mail
     */
    public function addBodyPart(MailPart $part): Mail
    {
        // Add mime part to the mail body
        array_push($this->bodyParts, $part);
        return $this;
    }

    /**
     * @param MailAttachment $attachment
     *
     * @return Mail
     */
    public function addInlineAttachment(MailAttachment $attachment): Mail
    {
        array_push($this->inlineAttachments, $attachment);
        return $this;
    }

    /**
     * @param MailAttachment $attachment
     *
     * @return Mail
     */
    public function addAttachment(MailAttachment $attachment): Mail
    {
        array_push($this->attachments, $attachment);
        return $this;
    }

    /**
     * Validates, generates and sends the message, using the given mail transport.
     *
     * @param TransportInterface $transport
     * @throws \RuntimeException
     */
    public function send(TransportInterface $transport)
    {
        // Make sure the mail has the mandatory header 'from' lines 1-4 (RFC 2822 sec 3.6.3)
        if ( ! $this->mail->isValid()) {
            throw new \RuntimeException('The mail is missing the from header(s).');
        }

        // Generate and set the body to the mail message
        $this->mail->getHeaders()->addHeaderLine(
            'Message-Id: ' . date('Ymd') . '.' . md5(microtime(true)) . '@mailer.oswebstyle.de'
        );

        $this->mail->setBody($this->generateMessage());

        // Send the mail
        /** @var Smtp $transport */
        $transport->send($this->mail);
    }

    /**
     * The structure of a mime mail looks like this:
     *
     * multipart/mixed
     * +-----------------------------+
     * |                             |
     * | multipart/related           |
     * | +-------------------------+ |
     * | |                         | |
     * | | multipart/alternative   | |
     * | | +---------------------+ | |
     * | | |                     | | |
     * | | | - Text mail (utf-8) | | |
     * | | | - Html mail (utf-8) | | |
     * | | |                     | | |
     * | | +---------------------+ | |
     * | |                         | |
     * | | - inline attachments    | |
     * | |                         | |
     * | +-------------------------+ |
     * |                             |
     * | - attachments               |
     * |                             |
     * +-----------------------------+
     *
     * @return MimeMessage
     *
     */
    protected function generateMessage(): MimeMessage
    {
        // Process body parts
        $mailPart = $this->generateAlternativePart();

        // Process inline attachments
        if (count($this->inlineAttachments) > 0) {
            $mailPart = $this->generateRelatedPart($mailPart);
        }

        // Process remaining attachments
        if (count($this->attachments) > 0) {
            $mailPart = $this->generateMixedPart($mailPart);
        }

        // Wrap the mail part in a message and return it
        $message = new MimeMessage();
        $message->setParts([ $mailPart ]);
        return $message;
    }

    /**
     * Generates a mail part of type 'multipart/alternative'
     * @return MimePart
     */
    protected function generateAlternativePart(): MimePart
    {
        $message = new MimeMessage();
        $boundary = $message->getMime()->boundary();
        foreach ($this->bodyParts as $bodyPart) {
            $bodyPart->setCharset('utf-8');
            $message->addPart($bodyPart->reveal()->setEncoding(Mime::ENCODING_8BIT));
        }
        return (new MimePart($message->generateMessage()))
            ->setBoundary($boundary)
            ->setEncoding($message->isMultiPart() ? Mime::ENCODING_7BIT : Mime::ENCODING_8BIT)
            ->setCharset('utf-8')
            ->setType($message->isMultiPart() ? Mime::MULTIPART_ALTERNATIVE : reset($this->bodyParts)->getMimeType());
    }

    /**
     * Generates a mail part of type 'multipart/related'.
     *
     * This mime type is used to indicate inline attachments like images.
     *
     * @param MimePart $alternativePart
     *
     * @return MimePart
     */
    protected function generateRelatedPart(MimePart $alternativePart): MimePart
    {
        if (count($this->inlineAttachments) === 0) {
            return $alternativePart;
        }

        $message = new MimeMessage();
        $boundary = $message->getMime()->boundary();
        $message->addPart($alternativePart);

        foreach ($this->inlineAttachments as $attachment) {
            $message->addPart($attachment->reveal()->setDisposition(Mime::DISPOSITION_INLINE));
        }

        return (new MimePart($message->generateMessage()))
            ->setBoundary($boundary)
            ->setEncoding(Mime::ENCODING_7BIT)
            ->setType(Mime::MULTIPART_RELATED . ';' . "\n" . ' type="multipart/alternative"');
    }

    /**
     * Generates a mail part of type 'multipart/mixed'.
     *
     * This mime type is used to distinguish related attachments and non-related attachments. This is at least the
     * theory. Some mail clients show inline attachments below the mail, if they are not referenced in the html part.
     *
     * @param MimePart $relatedPart
     *
     * @return MimePart
     */
    protected function generateMixedPart(MimePart $relatedPart): MimePart
    {
        if (count($this->attachments) === 0) {
            return $relatedPart;
        }

        $message = new MimeMessage();
        $boundary = $message->getMime()->boundary();
        $message->addPart($relatedPart);

        foreach ($this->attachments as $attachment) {
            $message->addPart($attachment->reveal());
        }

        return (new MimePart($message->generateMessage()))
            ->setBoundary($boundary)
            ->setEncoding(Mime::ENCODING_7BIT)
            ->setType(Mime::MULTIPART_MIXED);
    }
}
