<?php
/**
 * Copyright 2017 Oliver Schöndorn
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
