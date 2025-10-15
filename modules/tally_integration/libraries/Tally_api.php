<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tally_api
{
    private $CI;
    private $server_url;

    private $company_name;
    private $timeout = 30;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model(TALLY_INTEGRATION_MODULE_NAME . '/tally_integration_model');
        
        // Load settings
        $this->server_url = get_option('tally_server_url');
        $this->company_name = get_option('tally_company_name');
    }

    /**
     * Test connection to Tally server
     */
    public function test_connection()
    {
        try {
            $xml_request = $this->build_info_request();
            $response = $this->send_tally_request($xml_request);
            
            // Check for valid XML response from Tally
            if ($response && (
                strpos($response, 'COMPANYINFO') !== false ||
                strpos($response, 'COMPANY') !== false ||
                strpos($response, 'REMOTECMPINFO') !== false ||
                strpos($response, '<ENVELOPE>') !== false ||
                strpos($response, 'Tally.ERP 9 Server is Running') !== false ||
                strpos($response, '<RESPONSE>') !== false ||
                (strpos($response, '<?xml') !== false && strpos($response, 'TALLY') !== false)
            )) {
                return [
                    'success' => true,
                    'message' => 'Connection successful - Tally server responded',
                    'data' => $response
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Invalid response from Tally server. Response: ' . substr($response, 0, 200) . '...'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Build XML request to get ledger list from Tally - CORRECTED VERSION
     */
    public function build_ledger_list_request()
    {
        $company_name = get_option('tally_company_name');
        
        // This is the WORKING format that returns text data (like your successful test)
        $xmlRequest = '<ENVELOPE>
            <HEADER>
                <VERSION>1</VERSION>
                <TALLYREQUEST>Export</TALLYREQUEST>
                <TYPE>Collection</TYPE>
                <ID>List of Accounts</ID>
            </HEADER>
            <BODY>
                <DESC>
                    <STATICVARIABLES>
                        <SVCURRENTCOMPANY>' . $company_name . '</SVCURRENTCOMPANY>
                    </STATICVARIABLES>
                    <TDL>
                        <TDLMESSAGE>
                            <COLLECTION ISMODIFY="No" ISFIXED="No" ISINITIALIZE="No" ISOPTION="No" ISINTERNAL="No" NAME="List of Accounts">
                                <TYPE>Ledger</TYPE>
                                <FETCH>Name, Parent, OpeningBalance</FETCH>
                            </COLLECTION>
                        </TDLMESSAGE>
                    </TDL>
                </DESC>
            </BODY>
        </ENVELOPE>';
        
        return $xmlRequest;
    }

    /**
     * CORRECTED: Parse Tally ledger response - handles both XML and TEXT formats
     */
    public function parse_tally_ledgers($response)
    {
        $customers = [];
        $all_ledgers = [];
        
        try {
            // Log the response for debugging
            error_log("Tally Response Length: " . strlen($response) . " bytes");
            error_log("First 200 chars: " . substr($response, 0, 200));
            
            // Try text parsing FIRST (since that's your working format)
            $all_ledgers = $this->parse_text_ledger_response($response);
            error_log("Text parsing found: " . count($all_ledgers) . " ledgers");
            
            // If text parsing failed, try XML parsing
            if (empty($all_ledgers)) {
                error_log("Text parsing failed, trying XML parsing");
                $all_ledgers = $this->parse_xml_ledger_response($response);
                error_log("XML parsing found: " . count($all_ledgers) . " ledgers");
            }
            
            if (empty($all_ledgers)) {
                // Log the full response for analysis
                error_log("No ledgers found in either format. Full response: " . $response);
                throw new Exception('No ledger data found in Tally response. Response format not recognized.');
            }
            
            // IMPROVED: Enhanced customer identification logic
            foreach ($all_ledgers as $ledger_info) {
                if ($this->is_customer_ledger($ledger_info)) {
                    $customers[] = $this->format_customer_data($ledger_info);
                }
            }
            
            error_log("Customers found with primary logic: " . count($customers));
            
            // ENHANCED: Fallback logic specifically for Sundry Debtors
            if (empty($customers)) {
                error_log('No customers found with primary logic, trying Sundry Debtors specific matching...');
                
                // First try: Look specifically for any ledger with "Sundry" and "Debtor" in parent
                foreach ($all_ledgers as $ledger_info) {
                    $parent_lower = strtolower($ledger_info['parent']);
                    if ((strpos($parent_lower, 'sundry') !== false && strpos($parent_lower, 'debtor') !== false) ||
                        strpos($parent_lower, 'receivable') !== false) {
                        $customers[] = $this->format_customer_data($ledger_info);
                        error_log("Sundry Debtor fallback found: '{$ledger_info['name']}' under '{$ledger_info['parent']}'");
                    }
                }
                
                error_log("Sundry Debtors fallback found " . count($customers) . " customers");
                
                // Second fallback: More permissive matching
                if (empty($customers)) {
                    error_log('Still no customers found, trying permissive matching...');
                    
                    foreach ($all_ledgers as $ledger_info) {
                        if ($this->might_be_customer($ledger_info)) {
                            $customers[] = $this->format_customer_data($ledger_info);
                        }
                    }
                    
                    error_log("Permissive matching found " . count($customers) . " potential customers");
                }
            }
            
            // Log some sample customers for debugging
            if (!empty($customers)) {
                error_log("Sample customer: " . json_encode($customers[0]));
            }
            
        } catch (Exception $e) {
            error_log('Error parsing Tally ledgers: ' . $e->getMessage());
            error_log('Response was: ' . substr($response, 0, 500));
            throw new Exception('Error parsing customer data from Tally: ' . $e->getMessage());
        }

        error_log("Tally customer import: Found " . count($customers) . " customers out of " . count($all_ledgers) . " total ledgers");
        
        return $customers;
    }
    
    /**
     * Check if response is XML format
     */
    private function is_xml_response($response) {
        $trimmed = trim($response);
        
        // Check for XML indicators
        $has_xml_declaration = (strpos($trimmed, '<?xml') === 0);
        $has_envelope_tag = (strpos($trimmed, '<ENVELOPE>') === 0);
        $has_xml_tags = (strpos($trimmed, '<') !== false && strpos($trimmed, '>') !== false);
        
        // If it looks like your working text format, it's NOT XML
        // Your format: "ABC Manufacturing Ltd Sundry Debtors 0.00"
        $lines = explode("\n", $trimmed);
        $first_line = trim($lines[0]);
        
        // Check if first line matches text pattern: "Name Group Amount"
        if (preg_match('/^[A-Za-z0-9\s&\.\-]+\s+[A-Za-z\s]+\s+[\d\.,\-]+\s*$/', $first_line)) {
            return false; // This is text format
        }
        
        // Otherwise check for XML
        return ($has_xml_declaration || $has_envelope_tag || $has_xml_tags);
    }
    
    /**
     * Parse XML ledger response (original format)
     */
    private function parse_xml_ledger_response($xml_response) {
        $all_ledgers = [];
        
        // Suppress XML errors and use internal error handling
        $prev_setting = libxml_use_internal_errors(true);
        libxml_clear_errors();
        
        $xml = simplexml_load_string($xml_response);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            $error_msg = 'Invalid XML response from Tally';
            if (!empty($errors)) {
                $error_msg .= ': ' . $errors[0]->message;
            }
            
            // Restore previous setting
            libxml_use_internal_errors($prev_setting);
            
            // Instead of throwing error, return empty array and let text parser handle it
            error_log('XML parsing failed, will try text parsing: ' . $error_msg);
            return [];
        }

        // Navigate through Tally XML structure for ledgers
        if (isset($xml->BODY->EXPORTDATA->REQUESTDATA->TALLYMESSAGE)) {
            foreach ($xml->BODY->EXPORTDATA->REQUESTDATA->TALLYMESSAGE as $message) {
                if (isset($message->LEDGER)) {
                    $ledger = $message->LEDGER;
                    
                    // Get ledger name from either attributes or element
                    $ledger_name = '';
                    if (isset($ledger->attributes()->NAME)) {
                        $ledger_name = (string)$ledger->attributes()->NAME;
                    } elseif (isset($ledger->NAME)) {
                        $ledger_name = (string)$ledger->NAME;
                    }
                    
                    if (!empty($ledger_name)) {
                        $all_ledgers[] = [
                            'name' => $ledger_name,
                            'parent' => (string)$ledger->PARENT,
                            'phone' => (string)$ledger->LEDGERPHONE,
                            'email' => (string)$ledger->EMAIL,
                            'address' => isset($ledger->ADDRESS) ? (string)$ledger->ADDRESS : '',
                            'vat_number' => (string)$ledger->VATREGISTRATIONNO,
                        ];
                    }
                }
            }
        }
        
        // Restore previous setting
        libxml_use_internal_errors($prev_setting);
        
        return $all_ledgers;
    }
    
    /**
     * Parse text ledger response (your current working format)
     * Format: "LedgerName ParentGroup Balance"
     */
    private function parse_text_ledger_response($text_response) {
        $all_ledgers = [];
        $lines = explode("\n", trim($text_response));
        
        error_log("Parsing " . count($lines) . " lines from text response");
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            error_log("Line " . ($line_num + 1) . ": '" . $line . "'");
            
            // IMPROVED parsing with better regex patterns
            // Pattern 1: "Name Parent Balance" (your current format)
            // Making the parent group capture more flexible
            if (preg_match('/^(.+?)\s+(.*?)\s+([\d\.,\-\s]+)\s*$/', $line, $matches)) {
                $name = trim($matches[1]);
                $parent = trim($matches[2]);
                $balance = trim($matches[3]);
                
                error_log("Pattern 1 match - Name: '$name', Parent: '$parent', Balance: '$balance'");
                
                if (!empty($name) && !empty($parent)) {
                    $all_ledgers[] = [
                        'name' => $name,
                        'parent' => $parent,
                        'phone' => '',
                        'email' => '',
                        'address' => '',
                        'vat_number' => '',
                    ];
                }
            }
            // Pattern 2: Try different approach - split by multiple spaces
            elseif (preg_match('/^(.+?)(\s{2,})(.+?)(\s{2,})([\d\.,\-\s]*)\s*$/', $line, $matches)) {
                $name = trim($matches[1]);
                $parent = trim($matches[3]);
                
                error_log("Pattern 2 match - Name: '$name', Parent: '$parent'");
                
                if (!empty($name) && !empty($parent)) {
                    $all_ledgers[] = [
                        'name' => $name,
                        'parent' => $parent,
                        'phone' => '',
                        'email' => '',
                        'address' => '',
                        'vat_number' => '',
                    ];
                }
            }
            // Pattern 3: Simple two-part split (fallback)
            elseif (preg_match('/^(.+?)\s+(.+)$/', $line, $matches)) {
                $name = trim($matches[1]);
                $rest = trim($matches[2]);
                
                // Try to extract parent from the rest
                // Look for common parent patterns
                if (preg_match('/^(.+?)\s+([\d\.,\-\s]*)\s*$/', $rest, $rest_matches)) {
                    $parent = trim($rest_matches[1]);
                } else {
                    $parent = $rest;
                }
                
                error_log("Pattern 3 match - Name: '$name', Parent: '$parent'");
                
                // Make sure parent doesn't look like a balance amount
                if (!empty($name) && !empty($parent) && !preg_match('/^[\d\.,\-\s]+$/', $parent)) {
                    $all_ledgers[] = [
                        'name' => $name,
                        'parent' => $parent,
                        'phone' => '',
                        'email' => '',
                        'address' => '',
                        'vat_number' => '',
                    ];
                }
            }
            // Pattern 4: Tab-separated format
            elseif (strpos($line, "\t") !== false) {
                $parts = explode("\t", $line);
                if (count($parts) >= 2) {
                    $name = trim($parts[0]);
                    $parent = trim($parts[1]);
                    
                    error_log("Pattern 4 (tab) match - Name: '$name', Parent: '$parent'");
                    
                    if (!empty($name) && !empty($parent)) {
                        $all_ledgers[] = [
                            'name' => $name,
                            'parent' => $parent,
                            'phone' => '',
                            'email' => '',
                            'address' => '',
                            'vat_number' => '',
                        ];
                    }
                }
            }
            else {
                error_log("No pattern matched for line: '$line'");
            }
        }
        
        error_log("Text parsing extracted " . count($all_ledgers) . " ledgers");
        
        return $all_ledgers;
    }
    
    /**
     * Enhanced customer identification logic - IMPROVED FOR SUNDRY DEBTORS
     */
    private function is_customer_ledger($ledger_info) {
        $parent_lower = strtolower(trim($ledger_info['parent']));
        $name_lower = strtolower(trim($ledger_info['name']));
        
        // Log for debugging
        error_log("Checking if customer - Name: '{$ledger_info['name']}', Parent: '{$ledger_info['parent']}'");
        
        // ENHANCED: More comprehensive Sundry Debtors matching
        $is_sundry_debtor = (
            $parent_lower === 'sundry debtors' ||
            $parent_lower === 'sundry debtor' ||
            strpos($parent_lower, 'sundry debtors') !== false ||
            strpos($parent_lower, 'sundry debtor') !== false ||
            // Handle variations
            strpos($parent_lower, 'sundry') !== false && strpos($parent_lower, 'debtor') !== false
        );
        
        // Primary customer indicators (high confidence)
        $is_primary_customer = (
            $is_sundry_debtor ||
            strpos($parent_lower, 'customer') !== false ||
            strpos($parent_lower, 'client') !== false ||
            strpos($parent_lower, 'trade debtor') !== false ||
            strpos($parent_lower, 'account receivable') !== false ||
            strpos($parent_lower, 'receivable') !== false
        );
        
        // Secondary customer indicators (medium confidence)
        $is_secondary_customer = (
            strpos($parent_lower, 'party') !== false ||
            strpos($parent_lower, 'debtor') !== false ||
            (!empty($ledger_info['email']) && strpos($ledger_info['email'], '@') !== false) ||
            (!empty($ledger_info['phone']) && strlen(preg_replace('/[^0-9]/', '', $ledger_info['phone'])) >= 10)
        );
        
        // Exclude system/internal accounts
        $is_system_account = $this->is_system_account($ledger_info);
        
        $is_customer = ($is_primary_customer || $is_secondary_customer) && !$is_system_account;
        
        // Enhanced logging
        if ($is_sundry_debtor) {
            error_log("SUNDRY DEBTOR FOUND: '{$ledger_info['name']}' under '{$ledger_info['parent']}' - Is Customer: " . ($is_customer ? 'YES' : 'NO') . " - Is System: " . ($is_system_account ? 'YES' : 'NO'));
        } elseif ($is_primary_customer || $is_secondary_customer) {
            error_log("OTHER CUSTOMER FOUND: '{$ledger_info['name']}' under '{$ledger_info['parent']}' - Is Customer: " . ($is_customer ? 'YES' : 'NO') . " - Is System: " . ($is_system_account ? 'YES' : 'NO'));
        }
        
        return $is_customer;
    }
    
    /**
     * Permissive customer matching for fallback - ENHANCED FOR SUNDRY DEBTORS
     */
    private function might_be_customer($ledger_info) {
        $parent_lower = strtolower($ledger_info['parent']);
        $name_lower = strtolower($ledger_info['name']);
        
        // PRIORITY: Any variation of Sundry Debtors should be considered a customer
        $is_likely_sundry_debtor = (
            strpos($parent_lower, 'sundry') !== false ||
            strpos($parent_lower, 'debtor') !== false ||
            strpos($parent_lower, 'receivable') !== false ||
            strpos($parent_lower, 'customer') !== false ||
            strpos($parent_lower, 'client') !== false
        );
        
        // Very permissive - any ledger with contact info or reasonable parent
        $might_be_customer = (
            $is_likely_sundry_debtor ||
            (!empty($ledger_info['email']) && strpos($ledger_info['email'], '@') !== false) ||
            (!empty($ledger_info['phone']) && strlen(preg_replace('/[^0-9]/', '', $ledger_info['phone'])) >= 10) ||
            (!empty($ledger_info['address']) && strlen($ledger_info['address']) > 10) ||
            (!empty($ledger_info['vat_number'])) ||
            (strlen($ledger_info['name']) > 3 && !preg_match('/^(cash|bank|petty|misc|opening|closing)/i', $ledger_info['name']))
        );
        
        // Still exclude obvious system accounts (but be more careful)
        $is_obvious_system = (
            strpos($parent_lower, 'capital account') !== false ||
            strpos($parent_lower, 'reserves and surplus') !== false ||
            strpos($parent_lower, 'loans (liabilit') !== false ||
            strpos($parent_lower, 'bank accounts') !== false ||
            strpos($parent_lower, 'cash-in-hand') !== false ||
            strpos($parent_lower, 'indirect expense') !== false ||
            strpos($parent_lower, 'direct expense') !== false ||
            strpos($parent_lower, 'sales account') !== false ||
            strpos($parent_lower, 'purchase account') !== false ||
            strpos($parent_lower, 'fixed asset') !== false ||
            strpos($parent_lower, 'stock-in-hand') !== false ||
            strpos($name_lower, 'opening stock') !== false ||
            strpos($name_lower, 'closing stock') !== false ||
            strpos($name_lower, 'round off') !== false
        );
        
        $result = $might_be_customer && !$is_obvious_system;
        
        if ($is_likely_sundry_debtor) {
            error_log("PERMISSIVE: Potential Sundry Debtor - '{$ledger_info['name']}' under '{$ledger_info['parent']}' - Included: " . ($result ? 'YES' : 'NO'));
        }
        
        return $result;
    }
    
    /**
     * Check if ledger is a system account
     */
    private function is_system_account($ledger_info) {
        $parent_lower = strtolower($ledger_info['parent']);
        $name_lower = strtolower($ledger_info['name']);
        
        return (
            strpos($parent_lower, 'capital') !== false ||
            strpos($parent_lower, 'reserves') !== false ||
            strpos($parent_lower, 'surplus') !== false ||
            strpos($parent_lower, 'loan') !== false ||
            strpos($parent_lower, 'bank') !== false ||
            strpos($parent_lower, 'cash') !== false ||
            strpos($parent_lower, 'expense') !== false ||
            strpos($parent_lower, 'income') !== false ||
            strpos($parent_lower, 'indirect expense') !== false ||
            strpos($parent_lower, 'direct expense') !== false ||
            strpos($parent_lower, 'sales account') !== false ||
            strpos($parent_lower, 'purchase account') !== false ||
            strpos($parent_lower, 'fixed asset') !== false ||
            strpos($parent_lower, 'current asset') !== false ||
            strpos($parent_lower, 'current liabilit') !== false ||
            strpos($parent_lower, 'liability') !== false ||
            strpos($parent_lower, 'creditor') !== false ||
            strpos($parent_lower, 'payable') !== false ||
            strpos($parent_lower, 'tax') !== false ||
            strpos($parent_lower, 'duty') !== false ||
            strpos($name_lower, 'opening stock') !== false ||
            strpos($name_lower, 'closing stock') !== false ||
            strpos($name_lower, 'round off') !== false ||
            strpos($name_lower, 'tds') !== false ||
            strpos($name_lower, 'salary') !== false ||
            strpos($name_lower, 'discount') !== false
        );
    }
    
    /**
     * Format customer data for Perfex CRM
     */
    private function format_customer_data($ledger_info) {
        // Clean and validate phone number
        $phone = preg_replace('/[^0-9+\-\s\(\)]/', '', $ledger_info['phone']);
        
        // Clean email
        $email = filter_var(trim($ledger_info['email']), FILTER_VALIDATE_EMAIL);
        
        return [
            'name' => $ledger_info['name'],
            'parent' => $ledger_info['parent'],
            'phone' => $phone,
            'email' => $email ?: '',
            'address' => $ledger_info['address'],
            'vat_number' => $ledger_info['vat_number'],
        ];
    }

    /**
     * Format xml2array for Perfex CRM
     */
    public function xml2array($contents, $get_attributes = 1, $priority = 'tag')
    {
        if (!$contents) return array();
        if (!function_exists('xml_parser_create')) {
            // print "'xml_parser_create()' function not found!";
            return array();
        }
        // Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); // http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents) , $xml_values);
        xml_parser_free($parser);
        if (!$xml_values) return; //Hmm...
        // Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();
        $current = & $xml_array; //Refference
        // Go through the tags.
        $repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes, $value); //Remove existing values, or there will be trouble
            // This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data); //We could use the array by itself, but this cooler.
            $result = array();
            $attributes_data = array();
            if (isset($value)) {
                if ($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }
            // Set the attributes too.
            if (isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {                                   
                                    if ( $attr == 'ResStatus' ) {
                                        $current[$attr][] = $val;
                                    }
                    if ($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            // See tag status and do the needed.
                        //echo"<br/> Type:".$type;
            if ($type == "open") { //The starting of the tag '<tag>'
                $parent[$level - 1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if ($attributes_data) $current[$tag . '_attr'] = $attributes_data;
                                        //print_r($current[$tag . '_attr']);
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else { //There was another element with the same tag name
                    if (isset($current[$tag][0])) { //If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else { //This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        ); //This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            }
            elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
                // See if the key is already taken.
                if (!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data) $current[$tag . '_attr'] = $attributes_data;
                }
                else { //If taken, put all things inside a list(array)
                    if (isset($current[$tag][0]) and is_array($current[$tag])) { //If it is already an array...
                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else { //If it is not an array...
                        $current[$tag] = array(
                            $current[$tag],
                            $result
                        ); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes) {
                            if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset($current[$tag . '_attr']);
                            }
                            if ($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            }
            elseif ($type == 'close') { //End of tag '</tag>'
                $current = & $parent[$level - 1];
            }
        }
        return ($xml_array);
    }

    /**
     * Import customers from Tally to CRM - CORRECTED VERSION
     */
    public function import_customers_from_tally()
    {
        try {
            // Request ledger list from Tally using the working format
            $xml_request = $this->build_ledger_list_request();
            $response = $this->send_tally_request($xml_request);
            
            if (empty($response)) {
                return ['success' => false, 'message' => 'Empty response from Tally server'];
            }

            // Parse response (handles both XML and text formats)
            //$customers = $this->parse_tally_ledgers($response);
            $customers = $this->xml2array($response, $get_attributes = 3, $priority = 'tag');
            
            log_message("error", $response);
            if (empty($customers)) {
                // log_message("error", json_encode($customers));                      
                return ['success' => false, 'message' => 'No customer data found in Tally response'];
            }

            $ledgers = $customers['ENVELOPE']['BODY']['DATA']['COLLECTION']['LEDGER'] ?? [];

            $result = [];

            // Loop through ledgers (ignore the *_attr keys)
            foreach ($ledgers as $key => $ledger) {
                if (is_array($ledger) && strpos($key, '_attr') === false) {
                    $result[] = [
                        'name'   => $ledger['LANGUAGENAME.LIST']['NAME.LIST']['NAME'] ?? '',
                        'parent' => $ledger['PARENT'] ?? '',
                        'balance'=> $ledger['OPENINGBALANCE'] ?? '',
                    ];
                }
            }

            

            $imported_count = 0;
            $updated_count = 0;
            $errors = [];

            foreach ($result as $tally_customer) {
                try {
                    $result = $this->create_or_update_customer($tally_customer);
                    if ($result['action'] == 'created') {
                        $imported_count++;
                    } elseif ($result['action'] == 'updated') {
                        $updated_count++;
                    }
                    
                    // Log successful import
                    $this->CI->tally_integration_model->add_sync_log([
                        'sync_type' => 'import_customer',
                        'record_id' => $result['client_id'],
                        'status' => 'success',
                        'tally_response' => 'Customer ' . $result['action'] . ' successfully'
                    ]);
                } catch (Exception $e) {
                    $errors[] = "Error importing customer '{$tally_customer['name']}': " . $e->getMessage();
                    
                    // Log failed import
                    $this->CI->tally_integration_model->add_sync_log([
                        'sync_type' => 'import_customer',
                        'record_id' => 0,
                        'status' => 'error',
                        'error_message' => $e->getMessage(),
                        'tally_response' => json_encode($tally_customer)
                    ]);
                }
            }

            $message = "Import completed: {$imported_count} new customers, {$updated_count} updated";
            if (!empty($errors)) {
                $message .= ". Errors: " . count($errors);
            }

            return [
                'success' => true,
                'message' => $message,
                'imported' => $imported_count,
                'updated' => $updated_count,
                'errors' => $errors
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * CORRECTED: Create or update customer in CRM from Tally data
     */
    public function create_or_update_customer($tally_customer)
    {
        $this->CI->load->model('clients_model');
        
        try {
            // Validate required fields
            if (empty($tally_customer['name'])) {
                throw new Exception('Customer name is required');
            }
            
            // Clean and validate data
            $company_name = trim($tally_customer['name']);
            $email = !empty($tally_customer['email']) ? filter_var(trim($tally_customer['email']), FILTER_VALIDATE_EMAIL) : '';
            $phone = !empty($tally_customer['phone']) ? preg_replace('/[^0-9+\-\s\(\)]/', '', $tally_customer['phone']) : '';
            $address = !empty($tally_customer['address']) ? trim($tally_customer['address']) : '';
            $vat = !empty($tally_customer['vat_number']) ? trim($tally_customer['vat_number']) : '';
            
            // Check if customer already exists by name
            $existing_customer = null;
            
            // Try to find by company name (case-insensitive)
            $this->CI->db->where('LOWER(company)', strtolower($company_name));
            $existing_customer = $this->CI->db->get(db_prefix() . 'clients')->row();
            
            // If not found and email exists, try by email
            if (!$existing_customer && !empty($email)) {
                $this->CI->db->where('email', $email);
                $existing_customer = $this->CI->db->get(db_prefix() . 'clients')->row();
            }

            // Prepare customer data
            $customer_data = [
                'company' => $company_name,
                'phonenumber' => $phone,
                'address' => $address,
                'vat' => $vat,
            ];
            
            // Only add email if it's valid
            if (!empty($email)) {
                $customer_data['email'] = $email;
            }

            if ($existing_customer) {
                // Update existing customer
                $this->CI->db->where('userid', $existing_customer->userid);
                $success = $this->CI->db->update(db_prefix() . 'clients', $customer_data);
                
                if (!$success) {
                    throw new Exception('Failed to update customer in database');
                }
                
                return [
                    'action' => 'updated',
                    'client_id' => $existing_customer->userid
                ];
            } else {
                // Create new customer - add required fields
                $customer_data['datecreated'] = date('Y-m-d H:i:s');
                $customer_data['addedfrom'] = 0; // System import
                $customer_data['active'] = 1; // Active customer
                
                // Add default values for required fields if they don't exist
                if (!isset($customer_data['city'])) $customer_data['city'] = '';
                if (!isset($customer_data['state'])) $customer_data['state'] = '';
                if (!isset($customer_data['zip'])) $customer_data['zip'] = '';
                if (!isset($customer_data['country'])) $customer_data['country'] = 1; // Default country ID
                if (!isset($customer_data['billing_street'])) $customer_data['billing_street'] = $address;
                if (!isset($customer_data['billing_city'])) $customer_data['billing_city'] = '';
                if (!isset($customer_data['billing_state'])) $customer_data['billing_state'] = '';
                if (!isset($customer_data['billing_zip'])) $customer_data['billing_zip'] = '';
                if (!isset($customer_data['billing_country'])) $customer_data['billing_country'] = 1;
                if (!isset($customer_data['shipping_street'])) $customer_data['shipping_street'] = $address;
                if (!isset($customer_data['shipping_city'])) $customer_data['shipping_city'] = '';
                if (!isset($customer_data['shipping_state'])) $customer_data['shipping_state'] = '';
                if (!isset($customer_data['shipping_zip'])) $customer_data['shipping_zip'] = '';
                if (!isset($customer_data['shipping_country'])) $customer_data['shipping_country'] = 1;
                
                $success = $this->CI->db->insert(db_prefix() . 'clients', $customer_data);
                
                if (!$success) {
                    $db_error = $this->CI->db->error();
                    throw new Exception('Failed to create customer in database: ' . $db_error['message']);
                }
                
                $client_id = $this->CI->db->insert_id();
                
                if (!$client_id) {
                    throw new Exception('Failed to get new customer ID');
                }
                
                // Create primary contact if we have contact details
                if (!empty($email) || !empty($phone)) {
                    $contact_data = [
                        'userid' => $client_id,
                        'firstname' => $company_name, // Use company name as contact name
                        'lastname' => '',
                        'email' => $email,
                        'phonenumber' => $phone,
                        'title' => 'Primary Contact',
                        'is_primary' => 1,
                        'datecreated' => date('Y-m-d H:i:s'),
                        'active' => 1
                    ];
                    
                    $this->CI->db->insert(db_prefix() . 'contacts', $contact_data);
                }
                
                return [
                    'action' => 'created',
                    'client_id' => $client_id
                ];
            }
            
        } catch (Exception $e) {
            error_log('Error creating/updating customer: ' . $e->getMessage());
            error_log('Customer data: ' . json_encode($tally_customer));
            throw $e;
        }
    }

    /**
     * Format sync_customer for Perfex CRM
     */
    public function sync_customer($client_id)
    {
        $tally_version_name = get_option('tally_version_name');
        // Check if already synced recently
        if ($this->CI->tally_integration_model->is_recently_synced('customer', $client_id)) {
            //return ['success' => true, 'message' => 'Already synced recently'];
        }

        // Create sync log
        $log_id = $this->CI->tally_integration_model->add_sync_log([
            'sync_type' => 'customer',
            'record_id' => $client_id,
            'status' => 'pending'
        ]);

        try {
            // Get customer data
            $this->CI->load->model('clients_model');
            $customer = $this->CI->clients_model->get($client_id);
            if (!$customer) {
                throw new Exception('Customer not found');
            }

            // Build XML for customer (Ledger Master in Tally)
            $xml_request = $this->build_customer_xml($customer);
            if($tally_version_name == 'vesion_2.1'){
                $xml_request = $this->build_customer_xml($customer);
            }else{
                $xml_request = $this->build_customer_xmlversion6_2($customer);
            }

            
            // Send to Tally
            $response = $this->send_tally_request($xml_request);
            
            if ($this->is_success_response($response)) {
                $this->CI->tally_integration_model->mark_sync_successful($log_id, $response);
                return ['success' => true, 'message' => 'Customer synced successfully'];
            } else {
                $error_msg = $this->extract_error_message($response);
                $this->CI->tally_integration_model->mark_sync_failed($log_id, $error_msg);
                return ['success' => false, 'message' => $error_msg];
            }
            
        } catch (Exception $e) {
            $this->CI->tally_integration_model->mark_sync_failed($log_id, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format sync_invoice for Perfex CRM
     */
    public function sync_invoice($invoice_id)
    {
        // Check if already synced recently
        // if ($this->CI->tally_integration_model->is_recently_synced('invoice', $invoice_id)) {
        //     return ['success' => true, 'message' => 'Already synced recently'];
        // }

        // Create sync log
        $log_id = $this->CI->tally_integration_model->add_sync_log([
            'sync_type' => 'invoice',
            'record_id' => $invoice_id,
            'status' => 'pending'
        ]);

        try {
            // Get invoice data
            $this->CI->load->model('invoices_model');
            $invoice = $this->CI->invoices_model->get($invoice_id);

            
            if (!$invoice) {
                throw new Exception('Invoice not found');
            }

            // Build XML for invoice (Sales Voucher in Tally)
            $xml_request = $this->build_invoice_xml($invoice);
            
            // Send to Tally
            $response = $this->send_tally_request($xml_request);
            log_message("error", json_encode($response));

            
            if ($this->is_success_response($response)) {
                $this->CI->tally_integration_model->mark_sync_successful($log_id, $response);
                return ['success' => true, 'message' => 'Invoice synced successfully'];
            } else {
                $error_msg = $this->extract_error_message($response);
                $this->CI->tally_integration_model->mark_sync_failed($log_id, $error_msg);
                return ['success' => false, 'message' => $error_msg];
            }
            
        } catch (Exception $e) {
            $this->CI->tally_integration_model->mark_sync_failed($log_id, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format sync_payment for Perfex CRM
     */
    public function sync_payment($payment_id)
    {
        // Check if already synced recently
        if ($this->CI->tally_integration_model->is_recently_synced('payment', $payment_id)) {
            return ['success' => true, 'message' => 'Already synced recently'];
        }

        // Create sync log
        $log_id = $this->CI->tally_integration_model->add_sync_log([
            'sync_type' => 'payment',
            'record_id' => $payment_id,
            'status' => 'pending'
        ]);

        try {
            // Get payment data
            $this->CI->load->model('payments_model');
            $payment = $this->CI->payments_model->get($payment_id);
            
            if (!$payment) {
                throw new Exception('Payment not found');
            }

            // Build XML for payment (Receipt Voucher in Tally)
            $xml_request = $this->build_payment_xml($payment);
            
            // Send to Tally
            $response = $this->send_tally_request($xml_request);
            
            if ($this->is_success_response($response)) {
                $this->CI->tally_integration_model->mark_sync_successful($log_id, $response);
                return ['success' => true, 'message' => 'Payment synced successfully'];
            } else {
                $error_msg = $this->extract_error_message($response);
                $this->CI->tally_integration_model->mark_sync_failed($log_id, $error_msg);
                return ['success' => false, 'message' => $error_msg];
            }
            
        } catch (Exception $e) {
            $this->CI->tally_integration_model->mark_sync_failed($log_id, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Export data to XML format
     */
    public function export_to_xml($type, $start_date = null, $end_date = null)
    {
        switch ($type) {
            case 'customers':
                return $this->export_customers_xml($start_date, $end_date);
            case 'invoices':
                return $this->export_invoices_xml($start_date, $end_date);
            case 'payments':
                return $this->export_payments_xml($start_date, $end_date);
            default:
                return null;
        }
    }

    /**
     * Build customer XML for Tally (Ledger Master)
     */
    private function build_customer_xml($customer)
    {
        $xml = $this->get_xml_header();

        $ledgerName = !empty($customer->company) ? $customer->company : "Unnamed Ledger";

        $xml .= '<BODY>';
        $xml .= '<IMPORTDATA>';
        $xml .= '<REQUESTDESC>';
        $xml .= '<REPORTNAME>All Masters</REPORTNAME>';
        $xml .= '</REQUESTDESC>';
        $xml .= '<REQUESTDATA>';
        $xml .= '<TALLYMESSAGE xmlns:UDF="TallyUDF">';

        $xml .= '<LEDGER ACTION="Create">';

        $xml .= '<NAME>' . htmlspecialchars($ledgerName, ENT_XML1, 'UTF-8') . '</NAME>';
        // Parent group
        $xml .= '<PARENT>Sundry Debtors</PARENT>';

        // Basic flags
        $xml .= '<ISBILLWISEON>Yes</ISBILLWISEON>';
        $xml .= '<AFFECTSSTOCK>No</AFFECTSSTOCK>';

        // GSTIN and Registration Type
        $vat = htmlspecialchars($customer->vat, ENT_XML1, 'UTF-8');

        $xml .= '<GSTREGISTRATIONTYPE>Regular</GSTREGISTRATIONTYPE>';
        $xml .= '<PARTYGSTIN>'.$vat.'</PARTYGSTIN>';
        // GSTIN validation
        // Address list (address, city, state, country, pincode)

        if (!empty($customer->address)) {
            $xml .= '<ADDRESS.LIST TYPE="String">';
            $xml .= '<ADDRESS>' . htmlspecialchars($customer->address, ENT_XML1, 'UTF-8') . '</ADDRESS>';
            $xml .= '</ADDRESS.LIST>';
        }

        // State, Country, and Pincode
        if (!empty($customer->state)) {
            $xml .= '<LEDSTATENAME>' . htmlspecialchars($customer->state, ENT_QUOTES, 'UTF-8') . '</LEDSTATENAME>';
        }

        if (!empty($customer->country)) {
            $this->CI->db->select('*');
            $countries = $this->CI->db->get(db_prefix() . 'countries')->result_array();
            $this->CI->db->where('country_id', $customer->country);
            $country = $this->CI->db->get(db_prefix() . 'countries')->row();
            $xml .= '<COUNTRYNAME>' . htmlspecialchars($country->short_name, ENT_QUOTES, 'UTF-8') . '</COUNTRYNAME>';
        }

        if (!empty($customer->zip)) {
            $xml .= '<PINCODE>' . htmlspecialchars($customer->zip, ENT_QUOTES, 'UTF-8') . '</PINCODE>';
        }

        // Phone and Mobile
        if (!empty($customer->phonenumber)) {
            $phone = htmlspecialchars($customer->phonenumber, ENT_XML1, 'UTF-8');
            $xml .= '<LEDGERPHONE>' . $phone . '</LEDGERPHONE>';
            $xml .= '<LEDGERMOBILE>' . $phone . '</LEDGERMOBILE>';
        }

        // Email
        if (!empty($customer->email)) {
            $xml .= '<EMAIL>' . htmlspecialchars($customer->email, ENT_XML1, 'UTF-8') . '</EMAIL>';
        }

        // Language List
        $xml .= '<LANGUAGENAME.LIST>';
        $xml .= '<NAME.LIST TYPE="String">';
        $xml .= '<NAME>' . htmlspecialchars($ledgerName, ENT_XML1, 'UTF-8') . '</NAME>';
        $xml .= '</NAME.LIST>';
        $xml .= '<LANGUAGEID>1033</LANGUAGEID>';
        $xml .= '</LANGUAGENAME.LIST>';

        $xml .= '</LEDGER>';
        $xml .= '</TALLYMESSAGE>';
        $xml .= '</REQUESTDATA>';
        $xml .= '</IMPORTDATA>';
        $xml .= '</BODY>';
        $xml .= '</ENVELOPE>';

        return $xml;
    }

    private function build_customer_xmlversion6_2($customer)
    {
        $xml = $this->get_xml_header();

        $ledgerName = !empty($customer->company) ? $customer->company : "Unnamed Ledger";
        $vat = !empty($customer->vat) ? htmlspecialchars($customer->vat, ENT_XML1, 'UTF-8') : '';
        $state = !empty($customer->state) ? htmlspecialchars($customer->state, ENT_XML1, 'UTF-8') : '';
        $country = !empty($customer->country) ? htmlspecialchars($customer->country, ENT_XML1, 'UTF-8') : 'India';
        $address = !empty($customer->address) ? htmlspecialchars($customer->address, ENT_XML1, 'UTF-8') : '';
        $pincode = !empty($customer->zip) ? htmlspecialchars($customer->zip, ENT_XML1, 'UTF-8') : '';
        $phone = !empty($customer->phonenumber) ? htmlspecialchars($customer->phonenumber, ENT_XML1, 'UTF-8') : '';
        $email = !empty($customer->email) ? htmlspecialchars($customer->email, ENT_XML1, 'UTF-8') : '';
        $opening_balance = !empty($customer->opening_balance) ? htmlspecialchars($customer->opening_balance, ENT_XML1, 'UTF-8') : '0';
        $applicable_from = date('Ymd'); // Example: 20240401

        // Start Body
        $xml .= '<BODY>';
        $xml .= '<IMPORTDATA>';
        $xml .= '<REQUESTDESC>';
        $xml .= '<REPORTNAME>All Masters</REPORTNAME>';
        $xml .= '<STATICVARIABLES>';
        $xml .= '<SVCURRENTCOMPANY>##SVCURRENTCOMPANY</SVCURRENTCOMPANY>';
        $xml .= '</STATICVARIABLES>';
        $xml .= '</REQUESTDESC>';

        $xml .= '<REQUESTDATA>';
        $xml .= '<TALLYMESSAGE>';
        $xml .= '<LEDGER NAME="' . htmlspecialchars($ledgerName, ENT_XML1, 'UTF-8') . '" Action="Create">';

        // Basic Ledger Info
        $xml .= '<NAME>' . htmlspecialchars($ledgerName, ENT_XML1, 'UTF-8') . '</NAME>';
        $xml .= '<PARENT>Sundry Debtors</PARENT>';
        $xml .= '<OPENINGBALANCE>' . $opening_balance . '</OPENINGBALANCE>';

        // Mailing Details
        $xml .= '<LEDMAILINGDETAILS.LIST>';
        $xml .= '<APPLICABLEFROM>' . $applicable_from . '</APPLICABLEFROM>';
        $xml .= '<MAILINGNAME>' . htmlspecialchars($ledgerName, ENT_XML1, 'UTF-8') . '</MAILINGNAME>';
        if (!empty($state)) $xml .= '<STATE>' . $state . '</STATE>';
        if (!empty($country)) $xml .= '<COUNTRY>' . $country . '</COUNTRY>';
        if (!empty($pincode)) $xml .= '<PINCODE>' . $pincode . '</PINCODE>';
        if (!empty($email)) $xml .= '<EMAIL>' . $email . '</EMAIL>';
        if (!empty($phone)) {
            $xml .= '<PHONE>' . $phone . '</PHONE>';
            $xml .= '<MOBILE>' . $phone . '</MOBILE>';
        }
        if (!empty($address)) $xml .= '<ADDRESS>' . $address . '</ADDRESS>';
        $xml .= '</LEDMAILINGDETAILS.LIST>';

        // GST Registration Details
        $xml .= '<LEDGSTREGDETAILS.LIST>';
        $xml .= '<APPLICABLEFROM>' . $applicable_from . '</APPLICABLEFROM>';
        $xml .= '<GSTREGISTRATIONTYPE>Regular</GSTREGISTRATIONTYPE>';
        if (!empty($state)) $xml .= '<PLACEOFSUPPLY>' . $state . '</PLACEOFSUPPLY>';
        if (!empty($vat)) $xml .= '<GSTIN>' . $vat . '</GSTIN>';
        $xml .= '<ISOTHTERRITORYASSESSEE>No</ISOTHTERRITORYASSESSEE>';
        $xml .= '<CONSIDERPURCHASEFOREXPORT>No</CONSIDERPURCHASEFOREXPORT>';
        $xml .= '<ISTRANSPORTER>No</ISTRANSPORTER>';
        $xml .= '<ISCOMMONPARTY>No</ISCOMMONPARTY>';
        $xml .= '</LEDGSTREGDETAILS.LIST>';

        // Close Ledger
        $xml .= '</LEDGER>';
        $xml .= '</TALLYMESSAGE>';
        $xml .= '</REQUESTDATA>';
        $xml .= '</IMPORTDATA>';
        $xml .= '</BODY>';
        $xml .= '</ENVELOPE>';

        return $xml;
    }

    /**
     * Build generate_guid XML for Tally (Ledger Master)
     */
    private function generate_guid()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    /**
     * Build invoice XML for Tally (Sales Voucher)
     */
    private function build_invoice_xml($invoice)
    {
        // Get invoice items using the correct CRM helper function
        $this->CI->load->helper('sales');
        $invoice_items = get_items_by_type('invoice', $invoice->id);
        
        $xml = $this->get_xml_header();
        
        $xml .= '<BODY>' . "\n";
        $xml .= '<DESC></DESC>' . "\n";
        $xml .= '<DATA>' . "\n";
        $xml .= '<TALLYMESSAGE>' . "\n";
        $xml .= '<VOUCHER>' . "\n";
        
        $xml .= '<DATE>' . date('Ymd', strtotime($invoice->date)) . '</DATE>' . "\n";
        $xml .= '<VOUCHERTYPENAME>Sales</VOUCHERTYPENAME>' . "\n";
        $xml .= '<VOUCHERNUMBER>' . htmlspecialchars($invoice->number, ENT_XML1, 'UTF-8') . '</VOUCHERNUMBER>' . "\n";
        $xml .= '<PERSISTEDVIEW>Invoice Voucher View</PERSISTEDVIEW>' . "\n";
        $xml .= '<ISINVOICE>Yes</ISINVOICE>' . "\n";
        
        // Calculate totals
        $subtotal = $invoice->subtotal;
        $tax_total = $invoice->total - $invoice->subtotal;
        
        // LEDGER ENTRIES - Customer ledger comes FIRST with NEGATIVE amount
        $xml .= '<LEDGERENTRIES.LIST>' . "\n";
        $xml .= '<LEDGERNAME>' . htmlspecialchars($invoice->client->company, ENT_XML1, 'UTF-8') . '</LEDGERNAME>' . "\n";
        $xml .= '<ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>' . "\n";
        $xml .= '<ISPARTYLEDGER>Yes</ISPARTYLEDGER>' . "\n";
        $xml .= '<ISLASTDEEMEDPOSITIVE>Yes</ISLASTDEEMEDPOSITIVE>' . "\n";
        $xml .= '<AMOUNT>-' . number_format($invoice->total, 2, '.', '') . '</AMOUNT>' . "\n";
        
        // Bill allocation for customer ledger
        $xml .= '<BILLALLOCATIONS.LIST>' . "\n";
        $xml .= '<NAME>' . htmlspecialchars($invoice->number, ENT_XML1, 'UTF-8') . '</NAME>' . "\n";
        $xml .= '<BILLTYPE>New Ref</BILLTYPE>' . "\n";
        $xml .= '<AMOUNT>-' . number_format($invoice->total, 2, '.', '') . '</AMOUNT>' . "\n";
        $xml .= '</BILLALLOCATIONS.LIST>' . "\n";
        
        $xml .= '</LEDGERENTRIES.LIST>' . "\n";
        
        // Tax Ledger - if tax exists (POSITIVE amount)
        if ($tax_total > 0) {
            $tax_ledger_name = get_option('tally_tax_ledger_name');
            if (empty($tax_ledger_name)) {
                $tax_ledger_name = 'Duties & Taxes';
            }
            
            $xml .= '<LEDGERENTRIES.LIST>' . "\n";
            $xml .= '<LEDGERNAME>' . htmlspecialchars($tax_ledger_name, ENT_XML1, 'UTF-8') . '</LEDGERNAME>' . "\n";
            $xml .= '<ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>' . "\n";
            $xml .= '<AMOUNT>' . number_format($tax_total, 2, '.', '') . '</AMOUNT>' . "\n";
            $xml .= '</LEDGERENTRIES.LIST>' . "\n";
        }
        
        // INVENTORY ENTRIES - Each item with its own LIST
        if (!empty($invoice_items)) {
            foreach ($invoice_items as $item) {
                $item_amount = $item['rate'] * $item['qty'];
                
                $xml .= '<ALLINVENTORYENTRIES.LIST>' . "\n";
                $xml .= '<STOCKITEMNAME>' . htmlspecialchars($item['description'], ENT_XML1, 'UTF-8') . '</STOCKITEMNAME>' . "\n";
                $xml .= '<ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>' . "\n";
                $xml .= '<RATE>' . number_format($item['rate'], 2, '.', '') . '/nos</RATE>' . "\n";
                $xml .= '<AMOUNT>' . number_format($item_amount, 2, '.', '') . '</AMOUNT>' . "\n";
                $xml .= '<ACTUALQTY> ' . $item['qty'] . ' nos</ACTUALQTY>' . "\n";
                $xml .= '<BILLEDQTY> ' . $item['qty'] . ' nos</BILLEDQTY>' . "\n";
                
                // Link inventory to Sales ledger
                $xml .= '<ACCOUNTINGALLOCATIONS.LIST>' . "\n";
                $xml .= '<LEDGERNAME>Sales</LEDGERNAME>' . "\n";
                $xml .= '<ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>' . "\n";
                $xml .= '<AMOUNT>' . number_format($item_amount, 2, '.', '') . '</AMOUNT>' . "\n";
                $xml .= '</ACCOUNTINGALLOCATIONS.LIST>' . "\n";
                
                $xml .= '</ALLINVENTORYENTRIES.LIST>' . "\n";
            }
        } else {
            // If no items, create a simple ledger entry for Sales
            $xml .= '<LEDGERENTRIES.LIST>' . "\n";
            $xml .= '<LEDGERNAME>Sales</LEDGERNAME>' . "\n";
            $xml .= '<ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>' . "\n";
            $xml .= '<AMOUNT>' . number_format($subtotal, 2, '.', '') . '</AMOUNT>' . "\n";
            $xml .= '</LEDGERENTRIES.LIST>' . "\n";
        }
        
        $xml .= '</VOUCHER>' . "\n";
        $xml .= '</TALLYMESSAGE>' . "\n";
        $xml .= '</DATA>' . "\n";
        $xml .= '</BODY>' . "\n";
        $xml .= '</ENVELOPE>';
        
        return $xml;
    }

    /**
     * Build payment XML for Tally (Receipt Voucher)
     */
    private function build_payment_xml($payment)
    {
        $xml = $this->get_xml_header();
        
        $xml .= '<BODY>';
        $xml .= '<IMPORTDATA>';
        $xml .= '<REQUESTDESC>';
        $xml .= '<REPORTNAME>All Masters</REPORTNAME>';
        $xml .= '</REQUESTDESC>';
        $xml .= '<REQUESTDATA>';
        $xml .= '<TALLYMESSAGE xmlns:UDF="TallyUDF">';
        $xml .= '<VOUCHER REMOTEID="' . $payment->paymentid . '" VCHTYPE="Receipt" ACTION="Create">';
        $xml .= '<DATE>' . date('Ymd', strtotime($payment->date)) . '</DATE>';
        $xml .= '<VOUCHERTYPENAME>Receipt</VOUCHERTYPENAME>';
        $xml .= '<VOUCHERNUMBER>RCP-' . $payment->paymentid . '</VOUCHERNUMBER>';
        
        // Ledger entries
        $xml .= '<ALLLEDGERENTRIES.LIST>';
        $xml .= '<LEDGERNAME>' . htmlspecialchars($payment->paymentmode) . '</LEDGERNAME>';
        $xml .= '<ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>';
        $xml .= '<ISLASTDEEMEDPOSITIVE>Yes</ISLASTDEEMEDPOSITIVE>';
        $xml .= '<AMOUNT>' . number_format($payment->amount, 2, '.', '') . '</AMOUNT>';
        $xml .= '</ALLLEDGERENTRIES.LIST>';
        
        $xml .= '<ALLLEDGERENTRIES.LIST>';
        // Get customer name from invoice
        $this->CI->load->model('invoices_model');
        $invoice = $this->CI->invoices_model->get($payment->invoiceid);
        $xml .= '<LEDGERNAME>' . htmlspecialchars($invoice->client->company) . '</LEDGERNAME>';
        $xml .= '<ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>';
        $xml .= '<ISLASTDEEMEDPOSITIVE>No</ISLASTDEEMEDPOSITIVE>';
        $xml .= '<AMOUNT>-' . number_format($payment->amount, 2, '.', '') . '</AMOUNT>';
        $xml .= '</ALLLEDGERENTRIES.LIST>';
        
        $xml .= '</VOUCHER>';
        $xml .= '</TALLYMESSAGE>';
        $xml .= '</REQUESTDATA>';
        $xml .= '</IMPORTDATA>';
        $xml .= '</BODY>';
        $xml .= '</ENVELOPE>';
        
        return $xml;
    }

    /**
     * Build info request XML
     */
    private function build_info_request()
    {
        // Simple request that works with Tally ERP 9
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<ENVELOPE>' . "\n";
        $xml .= '<HEADER>' . "\n";
        $xml .= '<TALLYREQUEST>Export Data</TALLYREQUEST>' . "\n";
        $xml .= '</HEADER>' . "\n";
        $xml .= '<BODY>' . "\n";
        $xml .= '<EXPORTDATA>' . "\n";
        $xml .= '<REQUESTDESC>' . "\n";
        $xml .= '<REPORTNAME>List of Companies</REPORTNAME>' . "\n";
        $xml .= '<STATICVARIABLES>' . "\n";
        $xml .= '<SVEXPORTFORMAT>$SysName:XML</SVEXPORTFORMAT>' . "\n";
        $xml .= '</STATICVARIABLES>' . "\n";
        $xml .= '</REQUESTDESC>' . "\n";
        $xml .= '</EXPORTDATA>' . "\n";
        $xml .= '</BODY>' . "\n";
        $xml .= '</ENVELOPE>';
        
        return $xml;
    }

    /**
     * Get XML header
     */
    private function get_xml_header()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
               '<ENVELOPE>' . "\n" .
               '<HEADER>' . "\n" .
               '<TALLYREQUEST>Import Data</TALLYREQUEST>' . "\n" .
               '</HEADER>' . "\n";
    }

    /**
     * Send XML request to Tally server
     */
    public function send_tally_request($xml_data)
    {
        if (empty($this->server_url)) {
            throw new Exception('Tally server URL not configured');
        }

        $url = $this->server_url;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/xml',
                'Content-Length: ' . strlen($xml_data)
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "CURL Error: " . $error . "\n";
            return false;
        }

        if ($httpCode !== 200) {
            echo "HTTP Error: " . $httpCode . "\n";
            return false;
        }

        echo "Success! Response received:\n";
        echo "Response length: " . strlen($response) . " bytes\n";
        echo "Raw Response:\n" . $response . "\n\n";
        
        return $response;
    }

    /**
     * Check if response indicates success
     */
    private function is_success_response($response)
    {
        // Tally typically returns success indicators in the XML response
        return (
            strpos($response, '<CREATED>') !== false ||
            strpos($response, '<ALTERED>') !== false ||
            (strpos($response, '<ERROR>') === false && !empty($response))
        );
    }

    /**
     * Extract error message from response
     */
    private function extract_error_message($response)
    {
        if (preg_match('/<ERROR>(.*?)<\/ERROR>/s', $response, $matches)) {
            return strip_tags($matches[1]);
        }
        
        if (empty($response)) {
            return 'Empty response from Tally server';
        }
        
        return 'Unknown error in Tally response';
    }

    /**
     * Export customers to XML
     */
    private function export_customers_xml($start_date = null, $end_date = null)
    {
        $this->CI->load->model('clients_model');
        
        $where = [];
        if ($start_date) {
            $where['datecreated >='] = to_sql_date($start_date);
        }
        if ($end_date) {
            $where['datecreated <='] = to_sql_date($end_date);
        }
        
        $customers = $this->CI->clients_model->get('', $where);
        
        if (empty($customers)) {
            return null;
        }

        $xml = $this->get_xml_header();
        $xml .= '<BODY>';
        $xml .= '<IMPORTDATA>';
        $xml .= '<REQUESTDESC>';
        $xml .= '<REPORTNAME>All Masters</REPORTNAME>';
        $xml .= '</REQUESTDESC>';
        $xml .= '<REQUESTDATA>';
        
        foreach ($customers as $customer) {
            $xml .= $this->build_single_customer_xml($customer);
        }
        
        $xml .= '</REQUESTDATA>';
        $xml .= '</IMPORTDATA>';
        $xml .= '</BODY>';
        $xml .= '</ENVELOPE>';
        
        return $xml;
    }

    /**
     * Build single customer XML without full envelope
     */
    private function build_single_customer_xml($customer)
    {
        $xml = '<TALLYMESSAGE xmlns:UDF="TallyUDF">';
        $xml .= '<LEDGER NAME="' . htmlspecialchars($customer['company']) . '" ACTION="Create">';
        $xml .= '<NAME>' . htmlspecialchars($customer['company']) . '</NAME>';
        $xml .= '<PARENT>Sundry Debtors</PARENT>';
        $xml .= '<LEDGERPHONE>' . htmlspecialchars($customer['phonenumber']) . '</LEDGERPHONE>';
        $xml .= '<EMAIL>' . htmlspecialchars($customer['email']) . '</EMAIL>';
        
        if (!empty($customer['address'])) {
            $xml .= '<ADDRESS.LIST>';
            $xml .= '<ADDRESS>' . htmlspecialchars($customer['address']) . '</ADDRESS>';
            $xml .= '</ADDRESS.LIST>';
        }

        $xml .= '</LEDGER>';
        $xml .= '</TALLYMESSAGE>';
        
        return $xml;
    }


    /**
     * Export invoices to XML (placeholder)
     */
    private function export_invoices_xml($start_date = null, $end_date = null)
    {
        // Implementation similar to export_customers_xml but for invoices
        return null; // Implement based on your needs
    }

    /**
     * Export payments to XML (placeholder)
     */
    private function export_payments_xml($start_date = null, $end_date = null)
    {
        // Implementation similar to export_customers_xml but for payments
        return null; // Implement based on your needs
    }

    /**
     * CORRECTED: Debug method to analyze Tally ledger response
     */
    public function debug_tally_ledgers()
    {
        try {
            // Get configuration
            $server_url = get_option('tally_server_url');
            $company_name = get_option('tally_company_name');
            
            // Get the same request that import uses
            $xml_request = $this->build_ledger_list_request();
            $response = $this->send_tally_request($xml_request);
            
            // Enhanced debugging - capture request and response details
            $debug_info = [
                'server_url' => $server_url,
                'company_name' => $company_name,
                'request_xml' => $xml_request,
                'response_raw' => $response,
                'response_size' => strlen($response)
            ];
            
            if (empty($response)) {
                return [
                    'success' => false, 
                    'message' => 'Empty response from Tally server',
                    'debug_info' => $debug_info
                ];
            }
            
            // Try to parse the response using our corrected parser
            try {
                $customers = $this->parse_tally_ledgers($response);
                $all_ledgers = [];
                
                // If it's text format, also get all ledgers for analysis
                if (!$this->is_xml_response($response)) {
                    $all_ledgers = $this->parse_text_ledger_response($response);
                }
                
                // Group by parent for analysis
                $grouped_by_parent = [];
                foreach ($all_ledgers as $ledger) {
                    $parent = $ledger['parent'];
                    if (!isset($grouped_by_parent[$parent])) {
                        $grouped_by_parent[$parent] = [];
                    }
                    $grouped_by_parent[$parent][] = $ledger['name'];
                }
                
                // Sort groups by count (descending)
                uasort($grouped_by_parent, function($a, $b) { return count($b) - count($a); });
                
                return [
                    'success' => true,
                    'total_ledgers' => count($all_ledgers),
                    'potential_customers' => count($customers),
                    'groups' => $grouped_by_parent,
                    'sample_customers' => array_slice($customers, 0, 10),
                    'response_size' => strlen($response),
                    'response_format' => $this->is_xml_response($response) ? 'XML' : 'Text',
                    'debug_info' => $debug_info
                ];
                
            } catch (Exception $parseError) {
                return [
                    'success' => false,
                    'message' => 'Error parsing response: ' . $parseError->getMessage(),
                    'response_size' => strlen($response),
                    'response_format' => $this->is_xml_response($response) ? 'XML' : 'Text',
                    'debug_info' => $debug_info
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => $e->getMessage(),
                'response_size' => isset($response) ? strlen($response) : 0,
                'debug_info' => isset($debug_info) ? $debug_info : []
            ];
        }
    }

    public function executeHTTPRequest($company_name, $xmlRequest, $requestName) {
        $url = $this->server_url;
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xmlRequest,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'Content-Length: ' . strlen($xmlRequest),
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return false;
        }

        if ($httpCode !== 200) {
            return false;
        }
        return $response;
    }

    /**
     * Import invoices from Tally to CRM
     */
    public function cleanInvalidXmlChars($xmlString) {
        // Replace invalid &#4; with an empty string
        return preg_replace('/&#4;\s*/', '', $xmlString);
    }

    /**
     * Function to convert DOMNode to array
     */
    public function domToArray($node) {
        $output = [];
        
        // Handle element nodes
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $nodeName = $node->nodeName;
            
            // Handle attributes
            if ($node->hasAttributes()) {
                $output['@attributes'] = [];
                foreach ($node->attributes as $attr) {
                    $output['@attributes'][$attr->name] = $attr->value;
                }
            }
            
            // Handle child nodes
            if ($node->hasChildNodes()) {
                $children = [];
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        $childArray = $this->domToArray($child);
                        // Handle lists (e.g., elements ending in .LIST)
                        if (strpos($child->nodeName, '.LIST') !== false) {
                            $childName = str_replace('.LIST', '', $child->nodeName);
                            $children[$childName][] = $childArray;
                        } else {
                            $children[$child->nodeName] = $childArray;
                        }
                    } elseif ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                        $value = trim($child->nodeValue);
                        if ($value !== '') {
                            $output['value'] = $value;
                        }
                    }
                }
                
                // Merge children into output
                foreach ($children as $key => $value) {
                    if (isset($output[$key])) {
                        // If key exists, convert to array if not already
                        if (!is_array($output[$key]) || !isset($output[$key][0])) {
                            $output[$key] = [$output[$key]];
                        }
                        $output[$key][] = $value;
                    } else {
                        $output[$key] = $value;
                    }
                }
            }
            
            // Simplify output for nodes with only a value
            if (count($output) === 1 && isset($output['value'])) {
                return $output['value'];
            }
            
            return $output;
        }
        
        return null;
    }

    /**
     * Function to clean empty elements (optional)
     */
    public function cleanArray($array) {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->cleanArray($value);
                if (!empty($value)) {
                    $result[$key] = $value;
                }
            } elseif ($value !== '' && $value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Function import_invoices_from_tally
     */
    public function import_invoices_from_tally($date_from = null, $date_to = null)
    {
        try {
            // Request sales vouchers from Tally
            $response = $this->build_sales_voucher_list_request('01-Apr-2024', '31-Dec-2024');
            if (empty($response)) {
                return ['success' => false, 'message' => 'Empty response from Tally server'];
            }

            // Parse XML response
            //$invoices = $this->xml2array($response, $get_attributes = 3, $priority = 'tag');

            $cleanedXmlString = $this->cleanInvalidXmlChars($response);

            // Load cleaned XML into DOMDocument
            $dom = new DOMDocument();
            $dom->recover = true; // Enable recovery mode for minor errors
            $dom->strictErrorChecking = false;
            
            if (!$dom->loadXML($cleanedXmlString, LIBXML_NOCDATA)) {
                throw new Exception("Failed to parse XML with DOMDocument");
            }

            // Convert DOM to array
            $array = $this->domToArray($dom->documentElement);

            // Optionally clean the array to remove empty elements
            $cleanedArray = $this->cleanArray($array);

            $vouchers = $cleanedArray['BODY']['IMPORTDATA']['REQUESTDATA']['TALLYMESSAGE'];

            if (isset($vouchers['VOUCHER'])) {
                // single voucher
                $voucher_list = [$vouchers['VOUCHER']];
            } else {
                // multiple vouchers
                $voucher_list = array_column($vouchers, 'VOUCHER');
            }
            
            //log_message('error', 'Tally Sales Voucher Response Size: ' . json_encode($voucher_list));
            if (empty($voucher_list)) {
                log_message('error', $voucher_list);
                return ['success' => false, 'message' => 'No invoice data found in Tally response'];
            }

            $imported_count = 0;
            $updated_count = 0;
            $errors = [];

            foreach ($voucher_list as $voucher) {
                try {
                    
                    $tally_invoice = [
                        'customer_name'  => $voucher['PARTYNAME'],
                        'voucher_number' => $voucher['VOUCHERNUMBER'],
                        'date'           => date('Y-m-d', strtotime($voucher['DATE'])),
                        'amount'         => $voucher['LEDGERENTRIES'][0]['AMOUNT'], // careful: could be multiple entries
                        'items'          => []
                    ];

                    // Extract items
                    if (!empty($voucher['ALLINVENTORYENTRIES'])) {
                        foreach ($voucher['ALLINVENTORYENTRIES'] as $entry) {
                            $tally_invoice['items'][] = [
                                'description' => $entry['STOCKITEMNAME'],
                                'quantity'    => $entry['BILLEDQTY'],
                                'rate'        => $entry['RATE'],
                            ];
                        }
                    }
                    // Create or update invoice
                    $result = $this->create_or_update_invoice($tally_invoice);

                    if ($result['action'] == 'created') {
                        $imported_count++;
                    } elseif ($result['action'] == 'updated') {
                        $updated_count++;
                    }

                    // Log successful import
                    $this->CI->tally_integration_model->add_sync_log([
                        'sync_type'     => 'import_invoice',
                        'record_id'     => $result['invoice_id'],
                        'status'        => 'success',
                        'tally_response'=> 'Invoice ' . $result['action'] . ' successfully'
                    ]);

                } catch (Exception $e) {
                    $errors[] = "Error importing invoice: " . $e->getMessage();

                    // Log failed import
                    $this->CI->tally_integration_model->add_sync_log([
                        'sync_type'     => 'import_invoice',
                        'record_id'     => 0,
                        'status'        => 'error',
                        'error_message' => $e->getMessage(),
                        'tally_response'=> json_encode($voucher)
                    ]);
                }
            }

            $message = "Import completed: {$imported_count} new invoices, {$updated_count} updated";
            if (!empty($errors)) {
                $message .= ". Errors: " . count($errors);
            }

            return [
                'success' => true,
                'message' => $message,
                'imported' => $imported_count,
                'updated' => $updated_count,
                'errors' => $errors
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Build XML request to get sales voucher list from Tally
     */
    public function build_sales_voucher_list_request($date_from = null, $date_to = null)
    {
        $company_name = get_option('tally_company_name');

        $xmlRequest = '<ENVELOPE>
            <HEADER>
                <TALLYREQUEST>Export Data</TALLYREQUEST>
            </HEADER>
            <BODY>
                <EXPORTDATA>
                    <REQUESTDESC>
                        <REPORTNAME>Day Book</REPORTNAME>
                        <STATICVARIABLES>
                            <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
                            <SVFROMDATE>' . $date_from . '</SVFROMDATE>
                            <SVTODATE>' . $date_to . '</SVTODATE>
                            <SVCURRENTCOMPANY>' . $company_name . '</SVCURRENTCOMPANY>
                        </STATICVARIABLES>
                    </REQUESTDESC>
                </EXPORTDATA>
            </BODY>
        </ENVELOPE>';
        $response = $this->executeHTTPRequest($company_name, $xmlRequest, "Day Book - Full Data");
    
        return $response;
    }

    /**
     * Parse Tally sales voucher response to extract invoices - CORRECTED
     */
    public function parse_tally_sales_vouchers($response)
    {
        $invoices = [];
        
        try {
            error_log("Sales voucher response length: " . strlen($response));
            error_log("First 200 chars: " . substr($response, 0, 200));
            
            // Try text parsing first (like ledgers)
            $invoices = $this->parse_text_sales_vouchers($response);
            error_log("Text parsing found: " . count($invoices) . " invoices");
            
            // If text parsing failed, try XML parsing
            if (empty($invoices)) {
                error_log("Text parsing failed, trying XML parsing");
                $invoices = $this->parse_xml_sales_vouchers($response);
                error_log("XML parsing found: " . count($invoices) . " invoices");
            }
            
        } catch (Exception $e) {
            error_log('Error parsing Tally sales vouchers: ' . $e->getMessage());
            throw new Exception('Error parsing invoice data from Tally: ' . $e->getMessage());
        }

        return $invoices;
    }
    
    /**
     * Parse XML sales vouchers
     */
    private function parse_xml_sales_vouchers($xml_response) {
        $invoices = [];
        
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xml_response);
        
        if ($xml === false) {
            error_log('XML parsing failed for sales vouchers');
            return [];
        }

        // Navigate through Tally XML structure for vouchers
        if (isset($xml->BODY->EXPORTDATA->REQUESTDATA->TALLYMESSAGE)) {
            foreach ($xml->BODY->EXPORTDATA->REQUESTDATA->TALLYMESSAGE as $message) {
                if (isset($message->VOUCHER)) {
                    $voucher = $message->VOUCHER;
                    
                    $invoices[] = [
                        'voucher_number' => (string)$voucher->VOUCHERNUMBER,
                        'date' => $this->parse_tally_date((string)$voucher->DATE),
                        'customer_name' => (string)$voucher->PARTYLEDGERNAME,
                        'amount' => $this->parse_tally_amount((string)$voucher->AMOUNT),
                        'reference' => (string)$voucher->REFERENCE,
                        'items' => $this->parse_voucher_items($voucher)
                    ];
                }
            }
        }
        
        return $invoices;
    }

    /**
     * Parse text format sales vouchers - IMPROVED
     */
    private function parse_text_sales_vouchers($text_response) {
        $invoices = [];
        $lines = explode("\n", trim($text_response));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Try to parse various text formats
            // Format 1: "VoucherNo Date CustomerName Amount"
            if (preg_match('/^(\S+)\s+(\d{1,2}-\w{3}-\d{4}|\d{4}-\d{2}-\d{2})\s+(.+?)\s+([\d\.,\-]+)\s*$/', $line, $matches)) {
                $voucher_number = trim($matches[1]);
                $date = $this->parse_various_date_formats($matches[2]);
                $customer_name = trim($matches[3]);
                $amount = $this->parse_tally_amount($matches[4]);
                
                if (!empty($voucher_number) && !empty($customer_name) && $amount > 0) {
                    $invoices[] = [
                        'voucher_number' => $voucher_number,
                        'date' => $date,
                        'customer_name' => $customer_name,
                        'amount' => $amount,
                        'reference' => '',
                        'items' => []
                    ];
                }
            }
            // Format 2: "CustomerName VoucherNo Amount" (fallback)
            elseif (preg_match('/^(.+?)\s+(\S+)\s+([\d\.,\-]+)\s*$/', $line, $matches)) {
                $customer_name = trim($matches[1]);
                $voucher_number = trim($matches[2]);
                $amount = $this->parse_tally_amount($matches[3]);
                
                // Make sure voucher_number looks like a voucher (not amount)
                if (!empty($customer_name) && !empty($voucher_number) && 
                    !preg_match('/^[\d\.,\-]+$/', $voucher_number) && $amount > 0) {
                    $invoices[] = [
                        'voucher_number' => $voucher_number,
                        'date' => date('Y-m-d'), // Default to today
                        'customer_name' => $customer_name,
                        'amount' => $amount,
                        'reference' => '',
                        'items' => []
                    ];
                }
            }
        }
        
        return $invoices;
    }
    
    /**
     * Debug invoice import - shows what Tally returns for invoices
     */
    public function debug_tally_invoices($date_from = null, $date_to = null) {
        try {
            $xml_request = $this->build_sales_voucher_list_request($date_from, $date_to);
            $response = $this->send_tally_request($xml_request);
            
            $debug_info = [
                'request_xml' => $xml_request,
                'response_raw' => $response,
                'response_size' => strlen($response),
                'date_from' => $date_from,
                'date_to' => $date_to
            ];
            
            if (empty($response)) {
                return [
                    'success' => false,
                    'message' => 'Empty response from Tally server',
                    'debug_info' => $debug_info
                ];
            }
            
            // Try parsing
            try {
                $invoices = $this->parse_tally_sales_vouchers($response);
                
                return [
                    'success' => true,
                    'total_invoices' => count($invoices),
                    'sample_invoices' => array_slice($invoices, 0, 5),
                    'response_format' => $this->is_xml_response($response) ? 'XML' : 'Text',
                    'debug_info' => $debug_info
                ];
                
            } catch (Exception $parseError) {
                return [
                    'success' => false,
                    'message' => 'Error parsing response: ' . $parseError->getMessage(),
                    'debug_info' => $debug_info
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'debug_info' => isset($debug_info) ? $debug_info : []
            ];
        }
    }

    /**
     * Create or update invoice in CRM from Tally data
     */
    public function create_or_update_invoice($tally_invoice)
    {
        $this->CI->load->model('invoices_model');
        $this->CI->load->model('clients_model');
        
        try {
            // Find customer by name
            $this->CI->db->where('LOWER(company)', strtolower($tally_invoice['customer_name']));
            $customer = $this->CI->db->get(db_prefix() . 'clients')->row();
            
            if (!$customer) {
                throw new Exception("Customer '{$tally_invoice['customer_name']}' not found in CRM");
            }

            // Check if invoice already exists by number
            $this->CI->db->where('number', $tally_invoice['voucher_number']);
            $existing_invoice = $this->CI->db->get(db_prefix() . 'invoices')->row();

            $invoice_data = [
                'clientid' => $customer->userid,
                'number' => $tally_invoice['voucher_number'],
                'date' => $tally_invoice['date'],
                'duedate' => date('Y-m-d', strtotime($tally_invoice['date'] . ' +30 days')),
                'total' => $tally_invoice['amount'],
                'subtotal' => $tally_invoice['amount'],
                'status' => 1, // Unpaid
                'currency' => 1, // Default currency
            ];

            if ($existing_invoice) {
                // Update existing invoice
                $this->CI->db->where('id', $existing_invoice->id);
                $success = $this->CI->db->update(db_prefix() . 'invoices', $invoice_data);
                
                if (!$success) {
                    throw new Exception('Failed to update invoice in database');
                }
                
                return [
                    'action' => 'updated',
                    'invoice_id' => $existing_invoice->id
                ];
            } else {
                // Create new invoice
                $invoice_data['datecreated'] = date('Y-m-d H:i:s');
                $invoice_data['addedfrom'] = 0; // System import
                $invoice_data['hash'] = app_generate_hash();
                
                $success = $this->CI->db->insert(db_prefix() . 'invoices', $invoice_data);
                
                if (!$success) {
                    $db_error = $this->CI->db->error();
                    throw new Exception('Failed to create invoice in database: ' . $db_error['message']);
                }
                
                $invoice_id = $this->CI->db->insert_id();
                
                // Add invoice items if available
                if (!empty($tally_invoice['items'])) {
                    foreach ($tally_invoice['items'] as $item) {
                        $item_data = [
                            'rel_id' => $invoice_id,
                            'rel_type' => 'invoice',
                            'description' => $item['description'],
                            'long_description' => '',
                            'qty' => $item['quantity'],
                            'rate' => $item['rate'],
                            'unit' => '',
                            'order' => 1
                        ];
                        $this->CI->db->insert(db_prefix() . 'itemable', $item_data);
                    }
                } else {
                    // Create a default item if no items found
                    $item_data = [
                        'rel_id' => $invoice_id,
                        'rel_type' => 'invoice',
                        'description' => 'Imported from Tally - ' . $tally_invoice['voucher_number'],
                        'long_description' => '',
                        'qty' => 1,
                        'rate' => $tally_invoice['amount'],
                        'unit' => '',
                        'order' => 1
                    ];
                    $this->CI->db->insert(db_prefix() . 'itemable', $item_data);
                }
                
                return [
                    'action' => 'created',
                    'invoice_id' => $invoice_id
                ];
            }
        } catch (Exception $e) {
            error_log('Error creating/updating invoice: ' . $e->getMessage());
            error_log('Invoice data: ' . json_encode($tally_invoice));
            throw $e;
        }
    }

    public function map_tally_invoice($raw)
    {
        // Extract customer name
        $customer_name = $raw['PARTYNAME'] ?? $raw['PARTYLEDGERNAME'] ?? '';

        // Extract voucher number
        $voucher_number = $raw['VOUCHERNUMBER'] ?? '';

        // Convert DATE (YYYYMMDD → Y-m-d)
        $date = null;
        if (!empty($raw['DATE'])) {
            $date = DateTime::createFromFormat('Ymd', $raw['DATE']);
            $date = $date ? $date->format('Y-m-d') : null;
        }

        // Extract amount (sum from ledger entries if available)
        $amount = 0;
        if (!empty($raw['ALLLEDGERENTRIES.LIST'])) {
            $ledger_entries = is_assoc($raw['ALLLEDGERENTRIES.LIST'])
                ? [$raw['ALLLEDGERENTRIES.LIST']]
                : $raw['ALLLEDGERENTRIES.LIST'];

            foreach ($ledger_entries as $entry) {
                if (isset($entry['AMOUNT'])) {
                    $amount += (float)$entry['AMOUNT'];
                }
            }
        }

        // Extract items (inventory entries)
        $items = [];
        if (!empty($raw['ALLINVENTORYENTRIES.LIST'])) {
            $inventory_entries = is_assoc($raw['ALLINVENTORYENTRIES.LIST'])
                ? [$raw['ALLINVENTORYENTRIES.LIST']]
                : $raw['ALLINVENTORYENTRIES.LIST'];

            foreach ($inventory_entries as $entry) {
                $items[] = [
                    'description' => $entry['STOCKITEMNAME'] ?? 'Item',
                    'quantity'    => $entry['ACTUALQTY'] ?? 1,
                    'rate'        => $entry['RATE'] ?? 0,
                ];
            }
        }

        // Fallback: if no amount found, try top-level AMOUNT
        if ($amount == 0 && isset($raw['AMOUNT'])) {
            $amount = (float)$raw['AMOUNT'];
        }

        return [
            'customer_name'  => $customer_name,
            'voucher_number' => $voucher_number,
            'date'           => $date ?? date('Y-m-d'),
            'amount'         => $amount,
            'items'          => $items
        ];
    }

    /**
     * Helper: Parse Tally amount
     */
    public function parse_tally_amount($amount)
    {
        // Remove currency symbols and convert to float
        $amount = preg_replace('/[^\d\.\-]/', '', $amount);
        return floatval($amount);
    }

    /**
     * Helper: Parse voucher items
     */
    private function parse_voucher_items($voucher)
    {
        $items = [];
        
        if (isset($voucher->ALLINVENTORYENTRIES)) {
            foreach ($voucher->ALLINVENTORYENTRIES->children() as $inventory_entry) {
                if (isset($inventory_entry->STOCKITEMNAME)) {
                    $items[] = [
                        'description' => (string)$inventory_entry->STOCKITEMNAME,
                        'quantity' => floatval($inventory_entry->ACTUALQTY),
                        'rate' => floatval($inventory_entry->RATE),
                        'amount' => floatval($inventory_entry->AMOUNT)
                    ];
                }
            }
        }
        
        return $items;
    }

    /**
     * Helper: Extract payment mode from voucher
     */
    public function extract_payment_mode($voucher)
    {
        // Try to extract payment method from ledger entries
        $payment_mode = 'Cash'; // Default
        
        if (isset($voucher->ALLLEDGERENTRIES)) {
            foreach ($voucher->ALLLEDGERENTRIES->children() as $ledger_entry) {
                $ledger_name = (string)$ledger_entry->LEDGERNAME;
                if (strpos(strtolower($ledger_name), 'bank') !== false) {
                    $payment_mode = 'Bank Transfer';
                } elseif (strpos(strtolower($ledger_name), 'cash') !== false) {
                    $payment_mode = 'Cash';
                } elseif (strpos(strtolower($ledger_name), 'card') !== false) {
                    $payment_mode = 'Credit Card';
                }
            }
        }
        
        return $payment_mode;
    }
}