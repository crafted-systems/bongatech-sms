<?php
/**
 * Created by PhpStorm.
 * User: vincent
 * Date: 12/12/17
 * Time: 12:10 AM
 */

namespace CraftedSystems\Bongatech;

use Unirest\Request;
use Unirest\Request\Body;

class BongatechSMS
{
    /**
     * Base URL.
     *
     * @var string
     */
    const BASE_URL = 'http://197.248.4.47/smsapi/';

    /**
     * Send SMS endpoint.
     *
     * @var string
     */
    const SMS_ENDPOINT = 'submit.php';

    /**
     * Get Balance endpoint.
     *
     * @var string
     */
    const GET_BALANCE_ENDPOINT = 'balance.php';

    /**
     * token is generated by md5(password).
     *
     * @var string
     */
    protected $token;

    /**
     * timestamp is the current datetime.
     *
     * @var string
     */
    protected $timestamp;


    /**
     * sms configurations.
     *
     * @array config
     */
    protected $config;

    /**
     * the message(s) being sent (array of messages in case message is different for each user.
     *
     * @var array.
     */
    protected $message;

    /**
     * the recipients .
     *
     * @var array.
     */
    protected $recipient;

    /**
     * settings .
     *
     * @var array.
     */
    protected $settings;

    /**
     * BongatechSMS constructor.
     * @param $settings
     * @throws \Exception
     */
    public function __construct($settings)
    {
        $this->settings = (object)$settings;

        if (
            empty($this->settings->user_id) ||
            empty($this->settings->password) ||
            empty($this->settings->sender_id)
        ) {
            throw new \Exception('Please ensure that all Bongatech configuration variables have been set.');
        }

        $this->setTimestamp();
        $this->setToken();

    }

    /**
     * set the timestamp.
     */
    private function setTimestamp()
    {
        $this->timestamp = date('YmdHis');
    }

    /**
     * set the token.
     */
    private function setToken()
    {
        $this->token = md5($this->settings->password);
    }


    /**
     * @param $recipient
     * @param $message
     * @param null |array $params
     * @return string
     * @throws \Exception
     */
    public function send($recipient, $message, $params = null)
    {
        if (!is_string($message)) {

            throw new \Exception('The Message Should be a string');
        }

        if (!is_string($recipient)) {
            throw new \Exception('The Phone number should be a string');
        }


        $this->recipient = array(
            array(
                'MSISDN' => $recipient,
                'LinkID' => '',
                'SourceID' => !is_null($params) ? $params['SourceID'] : ''
            )
        );

        $this->message = array(
            array(
                'Text' => $message
            )
        );

        return $this->sendSMS($this->buildSendObject($this->recipient, $this->message));
    }


    /**
     * @param $body
     * @return \Unirest\Response
     * @throws \Unirest\Exception
     */
    private function sendSMS($body)
    {
        $endpoint = self::BASE_URL . self::SMS_ENDPOINT;

        $headers = [
            'Accept' => 'application/json',
        ];

        $response = Request::post($endpoint, $headers, Body::Json($body));

        return $response->body[0];
    }


    /**
     * @param $recipient
     * @param $message
     * @return array
     */
    private function buildSendObject($recipient, $message)
    {
        $body = [
            'AuthDetails' => [
                [
                    'UserID' => $this->settings->user_id,
                    'Token' => $this->token,
                    'Timestamp' => $this->timestamp,

                ],
            ],
            'MessageType' => [
                '3',
            ],
            'BatchType' => [
                '0',
            ],
            'SourceAddr' => [
                (string)$this->settings->sender_id,
            ],
            'MessagePayload' => $message,
            'DestinationAddr' => $recipient,
            'DeliveryRequest' => [
                [
                    'EndPoint' => $this->settings->call_back_url,
                    'Correlator' => (string)mt_rand(),
                ],
            ],
        ];

        return $body;
    }


    /**
     * @return mixed
     */
    public function getBalance()
    {
        $endpoint = self::BASE_URL . self::GET_BALANCE_ENDPOINT . '?UserID=' . $this->settings->user_id . '&Token=' . md5($this->settings->password);

        return Request::get($endpoint)->body->Balance;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public static function getDeliveryReport(\Illuminate\Http\Request $request)
    {
        return json_decode($request->getContent());
    }


}