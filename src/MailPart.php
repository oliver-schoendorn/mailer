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


use Zend\Mime\Mime;
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
     * @param MimePart|null $mimePart
     */
    public function __construct(string $id = '', MimePart $mimePart = null)
    {
        // Generate a random id
        if ( ! $id) {
            $id = bin2hex(openssl_random_pseudo_bytes(8));
        }

        $this->mimePart = ($mimePart ?? new MimePart())->setId(trim($id, '<>') . '@mailer.oswebstyle.de');
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
     * @param string $EOL
     *
     * @return string
     */
    public function getContent(string $EOL = Mime::LINEEND)
    {
        return $this->mimePart->getContent($EOL);
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
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimePart->getType();
    }

    /**
     * @param string $charset
     *
     * @return $this
     */
    public function setCharset(string $charset)
    {
        $this->mimePart->setCharset($charset);
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->mimePart->getCharset();
    }

    /**
     * @return MimePart
     */
    public function reveal(): MimePart
    {
        return $this->mimePart;
    }
}
