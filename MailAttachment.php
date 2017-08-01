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
