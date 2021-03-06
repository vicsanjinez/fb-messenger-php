<?php

namespace pimax\Messages;


/**
 * Class Message
 *
 * @package pimax\Messages
 */
class Message
{
    /**
     * @var null|string
     */
    protected $recipient = null;

    /**
     * @var null|string
     */
    protected $text = null;

    /**
     * @var bool
     */
    protected $user_ref = false;

    /**
     * Message constructor.
     *
     * @param string $recipient
     * @param string $text
     * @param string $tag - SHIPPING_UPDATE, RESERVATION_UPDATE, ISSUE_RESOLUTION
     * https://developers.facebook.com/docs/messenger-platform/send-api-reference/tags
     */
    public function __construct($recipient, $text, $user_ref = false, $tag = null)
    {
        $this->recipient = $recipient;
        $this->text = $text;
        $this->user_ref = $user_ref;
        $this->tag = $tag;
    }

    /**
     * Get message data
     *
     * @return array
     */
    public function getData()
    {
        return [
            'recipient' => $this->user_ref ? ['user_ref' => $this->recipient] : ['id' => $this->recipient],
            'message' => [
                'text' => $this->text
            ],
            'tag'=> $this->tag
        ];
    }

    /**
     * @param string $filename
     * @param string $contentType
     * @param string $postname
     * @return \CURLFile|string
     */
    protected function getCurlValue($filename, $contentType, $postname)
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $contentType, $postname);
        }

        // Use the old style if using an older version of PHP
        $value = "@{$this->filename};filename=" . $postname;
        if ($contentType) {
            $value .= ';type=' . $contentType;
        }

        return $value;
    }
}
