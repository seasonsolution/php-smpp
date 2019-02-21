<?php

namespace seasonsolution\phpsmpp;


/**
 * PHPSMPP - PHP SMS creation, transport and receive class.
 * 
 * @autor SeasonSolution
 * 
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
class smpplib
{
    /**
     * SMPP hosts.
     * Either a single hostname or multiple semicolon-delimited hostnames.
     * (e.g. "smpp.example.com;smpp.example2.com").
     * Hosts will be tried in order.
     *
     * @var string
     */
    public $host = '';
    
    /**
     * The default SMPP server port.
     * 
     * @var int
     */
    public $port = 3600;
    
    /**
     * SMPP username
     * 
     * @var string
     */
    public $username = '';
    
    /**
     * SMPP password
     * 
     * @var string
     */
    public $password = '';
    
    /**
     * The SMPP server timeout in seconds.
     * Default of 10 seconds.
     * 
     * @var int
     */
    public $timeout = 10000;
    
    /**
     * The SMPP server timeout in seconds to receive SMS.
     * Default of 60 seconds.
     * 
     * @var int
     */
    public $timeoutReceive = 60000;
    
    /**
     * SMPP class debug output mode.
     * Debug output level.
     * Options:
     * * `true` output commands
     * * `false` No output
     * 
     * @var bool
     */
    public $debug = false;
    
    /**
     * Data Coding
     * Encoding used. Default is GSM-7 (0), UCS-2 for messages containing characters outside GSM-7 (8)
     * 
     * @var string
     */
    public $datacoding = '';
    
    /**
     * Number To
     * Destination address - the MSISDN the SMS was sent to (your Long Number or Short Code)
     * 
     * @var string
     */
    public $numberTo = '';
    
    /**
     * Message content
     * 
     * @var string
     */
    public $message = 'H€llo world, this is my sms message.';
 
    /**
     * PHP SMPP library 
     *
     * SMS creation, transport class for PHP.
     *
     * @return bool
     */
    public function send()
    {
        // Prepare hosts
        $hosts = explode ( ';', $this -> host );
        
        // Construct transport and client
        $transport = new SocketTransport($hosts,$this -> port);
        $transport->setRecvTimeout($this -> timeout); // for this example wait up to 60 seconds for data
        $smpp = new SmppClient($transport);

        // Activate binary hex-output of server interaction
        $smpp->debug = $this -> debug;
        $transport->debug = $this -> debug;

        // Open the connection
        $transport->open();
        $smpp->bindTransmitter($this -> username, $this -> password);
        
        // Optional connection specific overrides
        //SmppClient::$sms_null_terminate_octetstrings = false;
        //SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
        //SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;
        
        // Prepare message
        $encodedMessage = GsmEncoder::utf8_to_gsm0338($this -> message);
        $from = new SmppAddress($this -> datacoding,SMPP::TON_ALPHANUMERIC);
        $to = new SmppAddress($this -> numberTo,SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);

        // Send
        $smpp->sendSMS($from,$to,$encodedMessage,$tags);

        // Close connection
        $smpp->close();
        
        // return
        return true;
    }
    
    /**
     * PHP SMPP library 
     *
     * SMS receive class for PHP.
     *
     * @return mixed
     */
    public function read()
    {
        // Prepare hosts
        $hosts = explode ( ';', $this -> host );
        
        // Construct transport and client
        $transport = new SocketTransport($hosts,$this -> port);
        $transport->setRecvTimeout($this -> timeoutReceive); // for this example wait up to 60 seconds for data
        $smpp = new SmppClient($transport);

        // Activate binary hex-output of server interaction
        $smpp->debug = $this -> debug;
        $transport->debug = $this -> debug;

        // Open the connection
        $transport->open();
        $smpp->bindReceiver($this -> username, $this -> password);

        // Read SMS and output
        $sms = $smpp->readSMS();

        // Close connection
        $smpp->close();
        
        // return
        return $sms;
    }
}