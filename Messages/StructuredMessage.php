<?php

namespace pimax\Messages;

/**
 * Class StructuredMessage
 *
 * @package pimax\Messages
 */
class StructuredMessage extends Message
{
    /**
     * Structured message button type
     */
    const TYPE_BUTTON = "button";

    /**
     * Structured message generic type
     */
    const TYPE_GENERIC = "generic";

    /**
     * Structured message list type
     */
    const TYPE_LIST = "list";

    /**
     * Structured message receipt type
     */
    const TYPE_RECEIPT = "receipt";

    /**
     * Generic message horizontal image aspect ratio
     */
    const IMAGE_ASPECT_RATIO_HORIZONTAL = "horizontal";

    /**
     * Generic message square image aspect ratio
     */
    const IMAGE_ASPECT_RATIO_SQUARE = "square";

    /**
     * @var null|string
     */
    protected $type = null;

    /**
     * @var null|string
     */
    protected $title = null;

    /**
     * @var null|string
     */
    protected $subtitle = null;

    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @var array
     */
    protected $buttons = [];

    /**
     * @var null|string
     */
    protected $recipient_name = null;

    /**
     * @var null|integer
     */
    protected $order_number = null;

    /**
     * @var string
     */
    protected $currency = "USD";

    /**
     * @var null|string
     */
    protected $payment_method = null;

    /**
     * @var null|string
     */
    protected $order_url = null;

    /**
     * @var null|integer
     */
    protected $timestamp = null;

    /**
     * @var array
     */
    protected $address = [];

    /**
     * @var array
     */
    protected $summary = [];

    /**
     * @var array
     */
    protected $adjustments = [];

    /**
     * @var string
     */
    protected $top_element_style = 'large';

    /**
     * @var string
     */
    protected $image_aspect_ratio = self::IMAGE_ASPECT_RATIO_HORIZONTAL;

    /**
     * @var array
     */
    protected $quick_replies = [];


    /**
     * StructuredMessage constructor.
     *
     * @param string $recipient
     * @param string $type
     * @param array  $data
     * @param string $tag - SHIPPING_UPDATE, RESERVATION_UPDATE, ISSUE_RESOLUTION
     * https://developers.facebook.com/docs/messenger-platform/send-api-reference/tags
     */
     public function __construct($recipient, $type, $data, $quick_replies = array(), $tag = null)
     {
         $this->recipient = $recipient;
         $this->type = $type;
         $this->quick_replies = $quick_replies;
         $this->tag = $tag;

        switch ($type)
        {
            case self::TYPE_BUTTON:
                $this->title = $data['text'];
                $this->buttons = $data['buttons'];
            break;

            case self::TYPE_GENERIC:
                $this->elements = $data['elements'];
                //aspect ratio used to render images specified by image_url in element objects
                //default is horizontal
                if(isset($data['image_aspect_ratio'])) {
                    $this->image_aspect_ratio = $data['image_aspect_ratio'];
                }
            break;

            case self::TYPE_LIST:
                $this->elements = $data['elements'];
                //allowed is a sinle button for the whole list
                if(isset($data['buttons'])){
                    $this->buttons = $data['buttons'];
                }
                //the top_element_style indicate if the first item is featured or not.
                //default is large
                if(isset($data['top_element_style'])){
                    $this->top_element_style = $data['top_element_style'];
                }
                //if the top_element_style is large the first element image_url MUST be set.
                if($this->top_element_style == 'large' && (!isset($data['elements'][0]->getData()['image_url']) || $data['elements'][0]->getData()['image_url'] == '')){
                    $message = 'Facbook require the image_url to be set for the first element if the top_element_style is large. set the image_url or change the top_element_style to compact.';
                    throw new \Exception($message);
                }
            break;

            case self::TYPE_RECEIPT:
                $this->recipient_name = $data['recipient_name'];
                $this->order_number = $data['order_number'];
                $this->currency = $data['currency'];
                $this->payment_method = $data['payment_method'];
                $this->order_url = $data['order_url'];
                $this->timestamp = $data['timestamp'];
                $this->elements = $data['elements'];
                $this->address = $data['address'];
                $this->summary = $data['summary'];
                $this->adjustments = $data['adjustments'];
            break;
        }
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        $result = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => $this->type
                ]
            ]
        ];

        foreach ($this->quick_replies as $qr) {
            $result['quick_replies'][] = $qr->getData();
        }

        switch ($this->type)
        {
            case self::TYPE_BUTTON:
                $result['attachment']['payload']['text'] = $this->title;
                $result['attachment']['payload']['buttons'] = [];

                foreach ($this->buttons as $btn) {
                    $result['attachment']['payload']['buttons'][] = $btn->getData();
                }

            break;

            case self::TYPE_GENERIC:
                $result['attachment']['payload']['elements'] = [];
                $result['attachment']['payload']['image_aspect_ratio'] = $this->image_aspect_ratio;

                foreach ($this->elements as $btn) {
                    $result['attachment']['payload']['elements'][] = $btn->getData();
                }
            break;

            case self::TYPE_LIST:
                $result['attachment']['payload']['elements'] = [];
                $result['attachment']['payload']['top_element_style'] = $this->top_element_style;
                //list items button
                foreach ($this->elements as $btn) {
                    $result['attachment']['payload']['elements'][] = $btn->getData();
                }
                //the whole list button
                foreach ($this->buttons as $btn) {
                    $result['attachment']['payload']['buttons'][] = $btn->getData();
                }
            break;

            case self::TYPE_RECEIPT:
                $result['attachment']['payload']['recipient_name'] = $this->recipient_name;
                $result['attachment']['payload']['order_number'] = $this->order_number;
                $result['attachment']['payload']['currency'] = $this->currency;
                $result['attachment']['payload']['payment_method'] = $this->payment_method;
                $result['attachment']['payload']['order_url'] = $this->order_url;
                $result['attachment']['payload']['timestamp'] = $this->timestamp;
                $result['attachment']['payload']['elements'] = [];

                foreach ($this->elements as $btn) {
                    $result['attachment']['payload']['elements'][] = $btn->getData();
                }

                $result['attachment']['payload']['address'] = $this->address->getData();
                $result['attachment']['payload']['summary'] = $this->summary->getData();
                $result['attachment']['payload']['adjustments'] = [];

                foreach ($this->adjustments as $btn) {
                    $result['attachment']['payload']['adjustments'][] = $btn->getData();
                }
            break;
        }

        return [
            'recipient' =>  [
                'id' => $this->recipient
            ],
            'message' => $result,
            'tag' => $this->tag
        ];
    }
}
