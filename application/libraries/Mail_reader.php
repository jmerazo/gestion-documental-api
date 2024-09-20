<?php
/**
 * Created by PhpStorm.
 * User: JairMunoz
 * Date: 31/05/2018
 * Time: 8:39 AM
 */

class Mail_reader
{
    private $connect_to;
    private $connection;
    private $user;
    private $password;
    private $emails;
    private $unseen;

    /*
     *
     */
    public $structure       = false;
    /// structure used to store files attached to a mail
    public $files;
    /// structure used to store alt files attached to a mail
    public $altfiles;

    public function __construct($params)
    {
    }

    public function connect($params)
    {
        $this->connect_to = $params['connect_to'];
        $this->user = $params['user'];
        $this->password = $params['password'];
        $this->connection = imap_open($this->connect_to, $this->user, $this->password);
        if (!$this->connection) {
            throw new Exception('Error de conexion al servidor de correo', 12);
        }
    }

    public function email($number)
    {
        $email = imap_fetch_overview($this->connection, $number, 0);
        return $email[0];
    }

    public function message($number)
    {
        $info = imap_fetchstructure($this->connection, $number, 0);

        if ($info->encoding == 3) {
            $message = base64_decode(imap_fetchbody($this->connection, $number, 1));
        } elseif ($info->encoding == 4) {
            $message = imap_qprint(imap_fetchbody($this->connection, $number, 1));
        } else {
            $message = imap_fetchbody($this->connection, $number, 1);
        }
        //$message = imap_fetchbody($this -> connection, $number, 2);
        return decode_qprint($message);
    }

    /**
     * @return resource
     */
    public function ultimo_mensaje()
    {
        //Check no.of.msgs
        $num = 1; #imap_num_msg($this->connection);
        //if there is a message in your inbox
        if ($num > 0) {
            //read that mail recently arrived
            echo imap_qprint(imap_body($this->connection, $num));
        }
        echo '<br>-------- <br>--';
        $mensaje = imap_fetchbody($this->connection, $num, 1);
        echo nl2br(strip_tags($mensaje, '<p>')); // Util para los mensajes HTML, los transforma en texto plano


        $struct = imap_fetchstructure($this->connection, $num);

        $i = 1;
        while (imap_fetchbody($this->connection, $num, $i)) {
            $struct->parts[$i - 1] = imap_bodystruct($this->connection, $num, $i);
            $j = 1;
            while (imap_fetchbody($this->connection, $num, "$i.$j")) {
                $struct->parts[$i - 1]->parts[$j - 1] = imap_bodystruct($this->connection, $num, "$i.$j");
                $j++;
            }
            $i++;
        }
        echo json_encode($struct, true);

    }



    public function list_messages()
    {
        $mails = [];
        $i = 0;
        $body='';
        $emails = imap_sort($this->connection, SORTDATE, 1, 0, 'ALL');

        //get messages id
        for ($i=1; ($i <= $tot); $i++) {
            $this->messages_uid[$i] = imap_uid($this->marubox, $i);
        }

        foreach ($emails as $email_number) {
            $mail = imap_headerinfo($this->connection, $email_number);
            #$overview = imap_fetch_overview($this->connection, $email_number, 0);
            var_dump($mail);
            $name = '';

            $body=$this->getBody($email_number);

            if (isset($mail->from[0]->personal)) {
                $name = $mail->from[0]->personal;
            }

            $mails[] = array(
                'id' => trim($mail->Msgno),
                'subject' => iconv_mime_decode($mail->subject),
                'date' => $mail->date,
                'from' => [
                    "name" => iconv_mime_decode($name),
                    'email' => $mail->from[0]->mailbox . '@' . $mail->from[0]->host
                ],
                'body' => $body,
                'otros' => $mail
            );
            if ($i++ > 19) {
                break;
            }
        }
        return $mails;
    }

    public function list_messages_1()
    {
        $i = 0;
        $emails = imap_search($this->connection, 'ALL');
        echo '<div class="row"><div class="col-sm-20"><table class="table table-bordered table-condensed">';
        foreach ($emails as $email_number) {
            echo '<tr>';
            $overview = imap_fetch_overview($this->connection, $email_number, 0);
            echo '<td>' . $i++ . '</td>';
            echo '<td> - ' . json_encode($overview[0], true) . '</td>';
            echo '<td>' . $overview[0]->from . '</td>';
            echo '</tr>';

        }
        echo '<table></div></div>';
    }



    /*
     * GLPI
     */

    /** function buildTicket - Builds,and returns, the major structure of the ticket to be entered.
     *
     * @param @param $uid UID of the message
     * @param $options   array    of possible options
     *
     * @return ticket fields array
     */
    function buildTicket($uid, $options = []) {
        global $CFG_GLPI;

        $play_rules = (isset($options['play_rules']) && $options['play_rules']);
        $head       = $this->getHeaders($uid); // Get Header Info Return Array Of Headers
        // **Key Are (subject,to,toOth,toNameOth,from,fromName)
        $tkt                 = [];
        $tkt['_blacklisted'] = false;
        // For RuleTickets
        $tkt['_mailgate']    = $options['mailgates_id'];

        // Use mail date if it's defined
        if ($this->fields['use_mail_date'] && isset($head['date'])) {
            $tkt['date'] = $head['date'];
        }
        // Detect if it is a mail reply
        $glpi_message_match = "/GLPI-([0-9]+)\.[0-9]+\.[0-9]+@\w*/";

        // Check if email not send by GLPI : if yes -> blacklist
        if (!isset($head['message_id'])
            || preg_match($glpi_message_match, $head['message_id'], $match)) {
            $tkt['_blacklisted'] = true;
            return $tkt;
        }
        // manage blacklist
        $blacklisted_emails   = Blacklist::getEmails();
        // Add name of the mailcollector as blacklisted
        $blacklisted_emails[] = $this->fields['name'];
        if (Toolbox::inArrayCaseCompare($head['from'], $blacklisted_emails)) {
            $tkt['_blacklisted'] = true;
            return $tkt;
        }

        // max size = 0 : no import attachments
        if ($this->fields['filesize_max'] > 0) {
            if (is_writable(GLPI_TMP_DIR)) {
                $tkt['_filename'] = $this->getAttached($uid, GLPI_TMP_DIR."/", $this->fields['filesize_max']);
                $tkt['_tag']      = $this->tags;
            } else {
                //TRANS: %s is a directory
                Toolbox::logInFile('mailgate', sprintf(__('%s is not writable'), GLPI_TMP_DIR."/"));
            }
        }

        //  Who is the user ?
        $tkt['_users_id_requester']                              = User::getOrImportByEmail($head[$this->getRequesterField()]);
        $tkt["_users_id_requester_notif"]['use_notification'][0] = 1;
        // Set alternative email if user not found / used if anonymous mail creation is enable
        if (!$tkt['_users_id_requester']) {
            $tkt["_users_id_requester_notif"]['alternative_email'][0] = $head[$this->getRequesterField()];
        }

        // Fix author of attachment
        // Move requester to author of followup
        $tkt['users_id'] = $tkt['_users_id_requester'];

        // Add to and cc as additional observer if user found
        if (count($head['ccs'])) {
            foreach ($head['ccs'] as $cc) {
                if (($cc != $head[$this->getRequesterField()])
                    && !Toolbox::inArrayCaseCompare($cc, $blacklisted_emails) // not blacklisted emails
                    && (($tmp = User::getOrImportByEmail($cc)) > 0)) {
                    $nb = (isset($tkt['_users_id_observer']) ? count($tkt['_users_id_observer']) : 0);
                    $tkt['_users_id_observer'][$nb] = $tmp;
                    $tkt['_users_id_observer_notif']['use_notification'][$nb] = 1;
                    $tkt['_users_id_observer_notif']['alternative_email'][$nb] = $cc;
                }
            }
        }

        if (count($head['tos'])) {
            foreach ($head['tos'] as $to) {
                if (($to != $head[$this->getRequesterField()])
                    && !Toolbox::inArrayCaseCompare($to, $blacklisted_emails) // not blacklisted emails
                    && (($tmp = User::getOrImportByEmail($to)) > 0)) {
                    $nb = (isset($tkt['_users_id_observer']) ? count($tkt['_users_id_observer']) : 0);
                    $tkt['_users_id_observer'][$nb] = $tmp;
                    $tkt['_users_id_observer_notif']['use_notification'][$nb] = 1;
                    $tkt['_users_id_observer_notif']['alternative_email'][$nb] = $to;
                }
            }
        }

        // Auto_import
        $tkt['_auto_import']           = 1;
        // For followup : do not check users_id = login user
        $tkt['_do_not_check_users_id'] = 1;
        $body                          = $this->getBody($uid);

        // Do it before using charset variable
        $head['subject']               = $this->decodeMimeString($head['subject']);
        $tkt['_head']                  = $head;

        if (!empty($this->charset)
            && !$this->body_converted
            && mb_detect_encoding($body) != 'UTF-8') {
            $body                 = Toolbox::encodeInUtf8($body, $this->charset);
            $this->body_converted = true;
        }

        if (!Toolbox::seems_utf8($body)) {
            $tkt['content'] = Toolbox::encodeInUtf8($body);
        } else {
            $tkt['content'] = $body;
        }

        // prepare match to find ticket id in headers
        // pattern: GLPI-{itemtype}-{items_id}
        // ex: GLPI-Ticket-26739
        $ref_match = "/GLPI-[A-Z]\w+-([0-9]+)/";

        // See In-Reply-To field
        if (isset($head['in_reply_to'])) {
            if (preg_match($ref_match, $head['in_reply_to'], $match)) {
                $tkt['tickets_id'] = intval($match[1]);
            }
        }

        // See in References
        if (!isset($tkt['tickets_id'])
            && isset($head['references'])) {
            if (preg_match($ref_match, $head['references'], $match)) {
                $tkt['tickets_id'] = intval($match[1]);
            }
        }

        // See in title
        if (!isset($tkt['tickets_id'])
            && preg_match('/\[.+#(\d+)\]/', $head['subject'], $match)) {
            $tkt['tickets_id'] = intval($match[1]);
        }

        $tkt['_supplier_email'] = false;
        // Found ticket link
        if (isset($tkt['tickets_id'])) {
            // it's a reply to a previous ticket
            $job = new Ticket();
            $tu  = new Ticket_User();
            $st  = new Supplier_Ticket();

            // Check if ticket  exists and users_id exists in GLPI
            if ($job->getFromDB($tkt['tickets_id'])
                && ($job->fields['status'] != CommonITILObject::CLOSED)
                && ($CFG_GLPI['use_anonymous_followups']
                    || ($tkt['_users_id_requester'] > 0)
                    || $tu->isAlternateEmailForITILObject($tkt['tickets_id'], $head[$this->getRequesterField()])
                    || ($tkt['_supplier_email'] = $st->isSupplierEmail($tkt['tickets_id'],
                        $head[$this->getRequesterField()])))) {

                if ($tkt['_supplier_email']) {
                    $tkt['content'] = sprintf(__('From %s'), $head[$this->getRequesterField()])."\n\n".$tkt['content'];
                }

                $header_tag      = NotificationTargetTicket::HEADERTAG;
                $header_pattern  = $header_tag . '.*' . $header_tag;
                $footer_tag      = NotificationTargetTicket::FOOTERTAG;
                $footer_pattern  = $footer_tag . '.*' . $footer_tag;

                $has_header_line = preg_match('/' . $header_pattern . '/s', $tkt['content']);
                $has_footer_line = preg_match('/' . $footer_pattern . '/s', $tkt['content']);

                if ($has_header_line && $has_footer_line) {
                    // Strip all contents between header and footer line
                    $tkt['content'] = preg_replace(
                        '/' . $header_pattern . '.*' . $footer_pattern . '/s',
                        '',
                        $tkt['content']
                    );
                } else if ($has_header_line) {
                    // Strip all contents between header line and end of message
                    $tkt['content'] = preg_replace(
                        '/' . $header_pattern . '.*$/s',
                        '',
                        $tkt['content']
                    );
                } else if ($has_footer_line) {
                    // Strip all contents between begin of message and footer line
                    $tkt['content'] = preg_replace(
                        '/^.*' . $footer_pattern . '/s',
                        '',
                        $tkt['content']
                    );
                }
            } else {
                // => to handle link in Ticket->post_addItem()
                $tkt['_linkedto'] = $tkt['tickets_id'];
                unset($tkt['tickets_id']);
            }
        }

        // Add message from getAttached
        if ($this->addtobody) {
            $tkt['content'] .= $this->addtobody;
        }

        //If files are present and content is html
        if (isset($this->files) && count($this->files) && $this->body_is_html) {
            $tkt['content'] = Ticket::convertContentForTicket($tkt['content'],
                $this->files + $this->altfiles,
                $this->tags);
        }

        // Clean mail content
        $tkt['content'] = $this->cleanMailContent($tkt['content']);

        $tkt['name'] = $this->textCleaner($head['subject']);
        if (!Toolbox::seems_utf8($tkt['name'])) {
            $tkt['name'] = Toolbox::encodeInUtf8($tkt['name']);
        }

        if (!isset($tkt['tickets_id'])) {
            // Which entity ?
            //$tkt['entities_id']=$this->fields['entities_id'];
            //$tkt['Subject']= $head['subject'];   // not use for the moment
            // Medium
            $tkt['urgency']  = "3";
            // No hardware associated
            $tkt['itemtype'] = "";
            // Mail request type

        } else {
            // Reopen if needed
            $tkt['add_reopen'] = 1;
        }

        $tkt['requesttypes_id'] = RequestType::getDefault('mail');

        if ($play_rules) {
            $rule_options['ticket']              = $tkt;
            $rule_options['headers']             = $head;
            $rule_options['mailcollector']       = $options['mailgates_id'];
            $rule_options['_users_id_requester'] = $tkt['_users_id_requester'];
            $rulecollection                      = new RuleMailCollectorCollection();
            $output                              = $rulecollection->processAllRules([], [],
                $rule_options);

            // New ticket : compute all
            if (!isset($tkt['tickets_id'])) {
                foreach ($output as $key => $value) {
                    $tkt[$key] = $value;
                }

            } else { // Followup only copy refuse data
                $tkt['requesttypes_id'] = RequestType::getDefault('mailfollowup');
                $tobecopied = ['_refuse_email_no_response', '_refuse_email_with_response'];
                foreach ($tobecopied as $val) {
                    if (isset($output[$val])) {
                        $tkt[$val] = $output[$val];
                    }
                }
            }
        }

        $tkt['content'] = LitEmoji::encodeShortcode($tkt['content']);

        $tkt = Toolbox::addslashes_deep($tkt);
        return $tkt;
    }

    /**
     * get the message structure if not already retrieved
     *
     * @param $mid : Message ID.
     **/
    function getStructure ($uid) {

        if (($uid != $this->uid)
            || !$this->structure) {
            $this->structure = imap_fetchstructure($this->connection, $uid, FT_UID);

            if ($this->structure) {
                $this->uid = $uid;
            }
        }
    }

    /**
     * @param $uid UID of the message
     **/
    function getAdditionnalHeaders($uid) {

        $head   = [];
        $header = explode("\n", imap_fetchheader($this->connection, $uid, FT_UID));

        if (is_array($header) && count($header)) {
            foreach ($header as $line) {
                // is line with additional header?
                if (preg_match("/^X-/i", $line)
                    || preg_match("/^Auto-Submitted/i", $line)
                    || preg_match("/^Received/i", $line)) {
                    // separate name and value
                    if (preg_match("/^([^:]*): (.*)/i", $line, $arg)) {
                        $key = Toolbox::strtolower($arg[1]);

                        if (!isset($head[$key])) {
                            $head[$key] = '';
                        } else {
                            $head[$key] .= "\n";
                        }

                        $head[$key] .= trim($arg[2]);
                    }
                }
            }
        }
        return $head;
    }


    /**
     * This function is use full to Get Header info from particular mail
     *
     * @param $uid UID of the message
     *
     * @return Return Associative array with following keys
     * subject   => Subject of Mail
     * to        => To Address of that mail
     * toOth     => Other To address of mail
     * toNameOth => To Name of Mail
     * from      => From address of mail
     * fromName  => Form Name of Mail
     **/
    function getHeaders($uid) {
        // Get Header info
        //$mail_header  = imap_header($this->connection, $mid);
        $mail_header = imap_rfc822_parse_headers(imap_fetchheader($this->connection, $uid, FT_UID));

        $sender       = $mail_header->from[0];
        $to           = $mail_header->to[0];
        $reply_to     = $mail_header->reply_to[0];
        $date         = date("Y-m-d H:i:s", strtotime($mail_header->date));

        $mail_details = [];

        if ((Toolbox::strtolower($sender->mailbox) != 'mailer-daemon')
            && (Toolbox::strtolower($sender->mailbox) != 'postmaster')) {

            // Construct to and cc arrays
            $tos = [];
            $ccs = [];
            if (count($mail_header->to)) {
                foreach ($mail_header->to as $data) {
                    $mailto = Toolbox::strtolower($data->mailbox).'@'.$data->host;
                    if ($mailto === $this->fields['name']) {
                        $to = $data;
                    }
                    $tos[] = $mailto;
                }
            }
            if (isset($mail_header->cc) && count($mail_header->cc)) {
                foreach ($mail_header->cc as $data) {
                    $ccs[] = Toolbox::strtolower($data->mailbox).'@'.$data->host;
                }
            }

            // secu on subject setting
            if (!isset($mail_header->subject)) {
                $mail_header->subject = '';
            }

            $mail_details = ['from'       => Toolbox::strtolower($sender->mailbox).'@'.$sender->host,
                'subject'    => $mail_header->subject,
                'reply-to'   => Toolbox::strtolower($reply_to->mailbox).'@'.$reply_to->host,
                'to'         => Toolbox::strtolower($to->mailbox).'@'.$to->host,
                'message_id' => $mail_header->message_id,
                'tos'        => $tos,
                'ccs'        => $ccs,
                'date'       => $date];

            if (isset($mail_header->references)) {
                $mail_details['references'] = $mail_header->references;
            }

            if (isset($mail_header->in_reply_to)) {
                $mail_details['in_reply_to'] = $mail_header->in_reply_to;
            }

            //Add additional headers in X-
            foreach ($this->getAdditionnalHeaders($uid) as $header => $value) {
                $mail_details[$header] = $value;
            }
        }

        return $mail_details;
    }


    /**
     * Get Mime type Internal Private Use
     *
     * @param $structure mail structure
     *
     * @return mime type
     **/
    function get_mime_type(&$structure) {

        // DO NOT REORDER IT
        $primary_mime_type = ["TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO",
            "IMAGE", "VIDEO", "OTHER"];

        if ($structure->subtype) {
            return $primary_mime_type[intval($structure->type)] . '/' . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }


    /**
     * Get Part Of Message Internal Private Use
     *
     * @param $stream       An IMAP stream returned by imap_open
     * @param $uid          The message UID
     * @param $mime_type    mime type of the mail
     * @param $structure    structure of the mail (false by default)
     * @param $part_number  The part number (false by default)
     *
     * @return data of false if error
     **/
    function get_part($stream, $uid, $mime_type, $structure = false, $part_number = false) {

        if ($structure) {
            if ($mime_type == $this->get_mime_type($structure)) {

                if (!$part_number) {
                    $part_number = "1";
                }

                $text = imap_fetchbody($stream, $uid, $part_number, FT_UID);

                if ($structure->encoding == ENCBASE64) {
                    $text =  imap_base64($text);
                } else if ($structure->encoding == ENCQUOTEDPRINTABLE) {
                    $text =  imap_qprint($text);
                }

                $text = str_replace(["\r\n", "\r"], "\n", $text); // Normalize line breaks

                $charset = null;

                foreach ($structure->parameters as $param) {
                    if (strtoupper($param->attribute) == 'CHARSET') {
                        $charset = strtoupper($param->value);
                    }
                }

                if (null !== $charset && 'UTF-8' !== $charset) {
                    if (in_array($charset, array_map('strtoupper', mb_list_encodings()))) {
                        $text                 = mb_convert_encoding($text, 'UTF-8', $charset);
                        $this->body_converted = true;
                    } else {
                        // Convert Windows charsets names
                        if (preg_match('/^WINDOWS-\d{4}$/', $charset)) {
                            $charset = preg_replace('/^WINDOWS-(\d{4})$/', 'CP$1', $charset);
                        }

                        if ($converted_test = iconv($charset, 'UTF-8//TRANSLIT', $text)) {
                            $text                 = $converted_test;
                            $this->body_converted = true;
                        }
                    }
                }

                return $text;
            }

            if ($structure->type == TYPEMULTIPART) {
                $prefix = "";

                foreach ($structure->parts as $index => $sub_structure) {
                    if ($part_number) {
                        $prefix = $part_number . '.';
                    }
                    $data = $this->get_part($stream, $uid, $mime_type, $sub_structure,
                        $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Public function : get attached documents in a mail
     *
     * @param $uid       UID of the message
     * @param $path      temporary path
     * @param $maxsize   of document to be retrieved
     *
     * @return array containing extracted filenames in file/_tmp
     **/
    function getAttached($uid, $path, $maxsize) {

        $this->getStructure($uid);
        $this->files     = [];
        $this->altfiles  = [];
        $this->addtobody = "";
        $this->getRecursiveAttached($uid, $path, $maxsize, $this->structure);

        return ($this->files);
    }


    /**
     * Get The actual mail content from this mail
     *
     * @param $uid : mail UID
     **/
    function getBody($uid) {
        // Get Message Body

        $this->getStructure($uid);
        $body = $this->get_part($this->connection, $uid, "TEXT/HTML", $this->structure);

        if (!empty($body)) {
            $this->body_is_html = true;
        } else {
            $body = $this->get_part($this->connection, $uid, "TEXT/PLAIN", $this->structure);
            $this->body_is_html = false;
        }

        if ($body == "") {
            return "";
        }

        return $body;
    }


    public function __destruct()
    {
        if ($this->connection) {
            imap_close($this->connection);
        }
    }

    public function __get($var)
    {
        $temp = strtolower($var);
        if (property_exists('mail_reader', $temp)) {
            return $this->$temp;
        }
        return NULL;
    }













    #ejemplos de como leer encabezados de mails

    /**
     * Get messages according to a search criteria
     *
     * @param    string    search criteria (RFC2060, sec. 6.4.4). Set to "UNSEEN" by default
     *                    NB: Search criteria only affects IMAP mailboxes.
     * @param    string    date format. Set to "Y-m-d H:i:s" by default
     * @return    mixed    array containing messages
     */
    public function get_messages($search_criteria = "UNSEEN", $date_format = "Y-m-d H:i:s")
    {
        //$msgs = imap_num_msg($this->imap_stream);
        $no_of_msgs = imap_num_msg($this->imap_stream);
        $messages = array();
        for ($i = 1; $i <= $no_of_msgs; $i++) {
            $header = imap_headerinfo($this->imap_stream, $i);
            $message_id = $header->message_id;
            $date = date($date_format, $header->udate);
            if (isset($header->from)) {
                $from = $header->from;
            } else {
                $from = FALSE;
            }
            $fromname = "";
            $fromaddress = "";
            $subject = "";
            if ($from != FALSE) {
                foreach ($from as $id => $object) {
                    if (isset($object->personal)) {
                        $fromname = $object->personal;
                    }
                    $fromaddress = $object->mailbox . "@" . $object->host;
                    if ($fromname == "") {
                        // In case from object doesn't have Name
                        $fromname = $fromaddress;
                    }
                }
            }
            if (isset($header->subject)) {
                $subject = $this->_mime_decode($header->subject);
            }
            // Read the message structure
            $structure = imap_fetchstructure($this->imap_stream, $i);
            if (!empty($structure->parts)) {
                for ($j = 0, $k = count($structure->parts); $j < $k; $j++) {
                    $part = $structure->parts[$j];
                    if ($part->subtype == 'PLAIN') {
                        $body = imap_fetchbody($this->imap_stream, $i, $j + 1);
                    }
                }
            } else {
                $body = imap_body($this->imap_stream, $i);
            }
            // Convert quoted-printable strings (RFC2045)
            $body = imap_qprint($body);
            // Convert to valid UTF8
            $body = htmlentities($body);
            $subject = htmlentities($subject);
            array_push($messages, array('message_id' => $message_id, 'date' => $date, 'from' => $fromname, 'email' => $fromaddress, 'subject' => $subject, 'body' => $body));
            // Mark Message As Read
            imap_setflag_full($this->imap_stream, $i, "\\Seen");
        }
        return $messages;
    }


    public function readEmails($sentTo = null, $bodyPart = null)
    {
        $host = '{imap.gmail.com:993/imap/ssl}INBOX';
        $spinner = new Spinner('Could not connect to Imap server.', 60, 10000);
        $inbox = $spinner->assertBecomesTrue(function () use ($host) {
            return @imap_open($host, $this->email, $this->password);
        });
        $emails = imap_search($inbox, 'TO ' . ($sentTo ? $sentTo : $this->email));
        if ($emails) {
            $messages = [];
            foreach ($emails as $n) {
                $structure = imap_fetchstructure($inbox, $n);
                if (!$bodyPart) {
                    $part = $this->findPart($structure, function ($part) {
                        return $part->subtype === 'HTML';
                    });
                } elseif (is_callable($bodyPart)) {
                    $part = $this->findPart($structure, $bodyPart);
                } else {
                    $part = $bodyPart;
                }
                $hinfo = imap_headerinfo($inbox, $n);
                $subject = $hinfo->subject;
                $message = ['subject' => $subject, 'body' => imap_fetchbody($inbox, $n, $part)];
                $messages[] = $message;
            }
            return $messages;
        } else {
            return [];
        }
    }

}

/*Funcion para decodificar los mensajes*/
function decode_qprint($str)
{
    $str = preg_replace("/\=([A-F][A-F0-9])/", "%$1", $str);
    $str = urldecode($str);
    $str = utf8_encode($str);
    return $str;
}