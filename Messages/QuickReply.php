<?php

namespace pimax\Messages;
/**
 * Class QuickReply
 *
 * @package pimax\Messages
 */
class QuickReply extends Message{
    /**
     * @var array
     */
    protected $quick_replies = array();

    /**
     * Message constructor.
     *
     * @param $recipient
     * @param $text - string
     * @param array $quick_replies - array of array("content_type","title","payload"),..,..
     * @param string $tag - SHIPPING_UPDATE, RESERVATION_UPDATE, ISSUE_RESOLUTION
     */
    public function __construct($recipient, $text, $quick_replies = array(), $tag = null)
    {
        $this->quick_replies = $quick_replies;
        $this->tag = $tag;
        parent::__construct($recipient,$text);
    }
    public function getData() {
        $result = [
            'recipient' =>  [
                'id' => $this->recipient
            ],
            'message' => [
                'text' => $this->text
            ],
            'tag'=> $this->tag
        ];

        foreach ($this->quick_replies as $qr) {
            if($qr instanceof QuickReplyButton){
                $result['message']['quick_replies'][] = $qr->getData();
            } else {
                $result['message']['quick_replies'][] = $qr;
            }
        }

        return $result;
    }
}
