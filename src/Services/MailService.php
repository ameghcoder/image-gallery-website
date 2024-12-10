<?php

namespace App\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

class MailService {
    private $host;       // SMTP host address (e.g., smtp.gmail.com)
    private $port;       // SMTP port (e.g., 587 for TLS, 465 for SSL)
    private $username;   // SMTP username (email address)
    private $password;   // SMTP password or app-specific password
    private $socket;     // Socket connection to the SMTP server

    public function __construct() {
        // Fetch SMTP credentials from the environment variables
        $this->host = $_ENV['MAIL_HOST'];
        $this->port = $_ENV['MAIL_PORT'];
        $this->username = $_ENV['MAIL_USER'];
        $this->password = $_ENV['MAIL_PASSWORD'];
    }

    /**
     * Reads the full response from the SMTP server.
     * SMTP servers often return multiline responses.
     * This method ensures we read the complete response.
     *
     * @return string Full server response
     */
    private function getResponse() {
        $response = "";
        while ($line = fgets($this->socket, 512)) {
            $response .= $line; // Append each line to the response
            if (substr($line, 3, 1) == " ") { // Check for end of the response
                break;
            }
        }

        if (feof($this->socket)) {
            throw new \Exception("Connection closed by server unexpectedly.");
        }

        return $response;
    }

    /**
     * Sends a command to the SMTP server and checks for the expected response code.
     * Logs the command and the server's response for debugging.
     *
     * @param string $command The SMTP command to send
     * @param int $expect The expected response code
     * @throws \Exception If the response code does not match the expected code
     */
    private function sendCommand($command, $expect = 250) {
        fwrite($this->socket, $command . "\r\n"); // Send the command to the server
        $response = $this->getResponse();        // Get the server's response

        // Log the command and response (useful for debugging)
        error_log("SMTP Command: $command");
        error_log("SMTP Response: $response");

        // Throw an exception if the response code doesn't match the expected value
        if (substr($response, 0, 3) != $expect) {
            throw new \Exception("SMTP Error: Expected $expect but got $response");
        }
    }

    /**
     * Sends an email using the SMTP protocol.
     *
     * @param string $from The sender's email address
     * @param string $to The recipient's email address
     * @param string $subject The email subject
     * @param string $body The email body
     * @return bool True if the email is sent successfully, false otherwise
     */
    public function sendEmail($from, $to, $subject, $body) {
        try {
            // Determine protocol for SSL (e.g., ssl:// for port 465)
            $protocol = ($this->port == 465) ? "ssl://" : "";

            // Establish a socket connection to the SMTP server
            $this->socket = fsockopen($protocol . $this->host, $this->port, $errno, $errstr, 30);
            if (!$this->socket) {
                throw new \Exception("Connection failed: $errstr ($errno)");
            }

            stream_set_timeout($this->socket, 30); // Set timeout  for read/write operations

            // Log the initial server response (220 code expected)
            $this->getResponse();

            // Send EHLO to introduce the client to the server
            $this->sendCommand("EHLO " . gethostname());

            // If using TLS (port 587), start encryption
            if ($this->port == 587) {
                $this->sendCommand("STARTTLS", 220); // Initiate TLS handshake
                stream_socket_enable_crypto(
                    $this->socket, 
                    true, 
                    STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                $this->sendCommand("EHLO " . gethostname()); // Reintroduce after TLS starts
            }

            // Authenticate with the server using AUTH LOGIN
            $this->sendCommand("AUTH LOGIN", 334); // Server expects Base64-encoded username
            $this->sendCommand(base64_encode($this->username), 334);
            $this->sendCommand(base64_encode($this->password), 235);

            // Specify the sender's email address
            $this->sendCommand("MAIL FROM:<$from>");

            // Specify the recipient's email address
            $this->sendCommand("RCPT TO:<$to>");

            // Indicate the start of the email content
            $this->sendCommand("DATA", 354);

            // Construct the email headers
            $headers = "From: $from\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "Date: " . date(DATE_RFC2822) . "\r\n";
            $headers .= "Message-ID: <" . uniqid() . "@". $_ENV["DOMAIN_NAME"] .">\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";

            // Send the email content and indicate end of data with "\r\n."
            $this->sendCommand($headers . $body . "\r\n.", 250);

            // Quit the SMTP session
            $this->sendCommand("QUIT", 221);

            // Close the socket connection
            fclose($this->socket);

            return true; // Email sent successfully
        } catch (\Exception $e) {
            // Log errors using Monolog
            $log = new Logger('Mail');
            $log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', LogLevel::DEBUG, true));
            $log->error("Email sending failed", [
                'to' => $to,
                'from' => $from,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString()
            ]);

            return false; // Email failed to send
        } finally {
            // Ensure the socket is closed in case of an error
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
        }
    }
}
