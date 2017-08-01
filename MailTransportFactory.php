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


use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Transport\InMemory as InMemoryTransport;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class MailTransportFactory
{
    /**
     * @param string $serverName
     * @param string $hostName
     * @param int    $port
     *
     * @return TransportInterface|SmtpTransport
     */
    public function createSmtpTransport(string $serverName, string $hostName, int $port): TransportInterface
    {
        // Setup smtp options
        $options = new SmtpOptions([
            'name' => $serverName,
            'host' => $hostName,
            'port' => $port
        ]);

        // Returns transport instance
        return new SmtpTransport($options);
    }

    /**
     * @param string      $username
     * @param string      $password
     * @param string|null $encryption
     * @param string      $serverName
     * @param string      $hostName
     * @param int         $port
     *
     * @return TransportInterface|SmtpTransport
     */
    public function createSmtpTransportWithLogin(
        string $username,
        string $password,
        string $encryption = null,
        string $serverName,
        string $hostName,
        int $port
    ): TransportInterface
    {
        // Setup smtp options
        $options = [
            'name' => $serverName,
            'host' => $hostName,
            'port' => $port,
            'connection_class'  => 'login',
            'connection_config' => [
                'username' => $username,
                'password' => $password
            ]
        ];

        if ($encryption && ! empty($encryption)) {
            $options['connection_config']['ssl'] = $encryption;
        }

        // Return transport instance
        return new SmtpTransport(new SmtpOptions($options));
    }

    /**
     * Class for sending email via the PHP internal mail() function
     *
     * @return TransportInterface|SendmailTransport
     */
    public function createSendMailTransport(): TransportInterface
    {
        return new SendmailTransport();
    }

    /**
     * This transport will just store the message in memory.  It is helpful
     * when unit testing, or to prevent sending email when in development or
     * testing.
     *
     * @return TransportInterface|InMemoryTransport
     */
    public function createInMemoryTransport(): TransportInterface
    {
        return new InMemoryTransport();
    }
}
