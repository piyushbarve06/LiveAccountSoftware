# Tally Integration ODBC Collection Details

## 📊 **ODBC Connection Configuration**

### **Method 1: Direct ODBC Connection**
```php
// ODBC Connection Details
$odbc_config = [
    'driver' => 'TallyODBC64_9002',  // For 64-bit system
    'port' => 9002,                  // Default Tally ODBC port
    'host' => 'localhost',
    'timeout' => 30,
    'company_name' => '', // Set in configuration
];

// Connection String Format
$connection_string = "Driver={TallyODBC64_9002};Server=localhost:9002;Company={$company_name}";
```

### **Method 2: HTTP XML Gateway (Recommended)**
```php
// HTTP XML Gateway Configuration
$gateway_config = [
    'server_url' => 'http://localhost:9002',  // TallyPrime Gateway URL
    'method' => 'POST',
    'content_type' => 'application/xml; charset=utf-8',
    'timeout' => 60,
    'company_name' => '', // Set in configuration
];
```

## 🔍 **Data Collection Methods**

### **1. Standard Collection Export**
```xml
<ENVELOPE>
    <HEADER>
        <TALLYREQUEST>Export Data</TALLYREQUEST>
    </HEADER>
    <BODY>
        <EXPORTDATA>
            <REQUESTDESC>
                <REPORTNAME>All Masters</REPORTNAME>
                <STATICVARIABLES>
                    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
                    <SVFROMDATE type="Date">1-4-2020</SVFROMDATE>
                    <SVTODATE type="Date">31-3-2025</SVTODATE>
                </STATICVARIABLES>
            </REQUESTDESC>
        </EXPORTDATA>
    </BODY>
</ENVELOPE>
```

### **2. Ledger Collection with TDL**
```xml
<ENVELOPE>
    <HEADER>
        <TALLYREQUEST>Export Data</TALLYREQUEST>
    </HEADER>
    <BODY>
        <EXPORTDATA>
            <REQUESTDESC>
                <REPORTNAME>DSP_LEDGER</REPORTNAME>
                <STATICVARIABLES>
                    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
                    <SVFROMDATE type="Date">1-Apr-2020</SVFROMDATE>
                    <SVTODATE type="Date">31-Mar-2025</SVTODATE>
                </STATICVARIABLES>
                <TDL>
                    <TDLMESSAGE>
                        <COLLECTION NAME="MyLedgerCollection">
                            <TYPE>Ledger</TYPE>
                            <NATIVEMETHOD>*</NATIVEMETHOD>
                            <FETCH>Name, Parent, Alias, OpeningBalance, ClosingBalance, Address, Phone, Email, IncomeTaxNumber, PartyGSTIN, GUID, CreatedOn, AlteredOn</FETCH>
                        </COLLECTION>
                    </TDLMESSAGE>
                </TDL>
            </REQUESTDESC>
        </EXPORTDATA>
    </BODY>
</ENVELOPE>
```

### **3. Customer Data Collection**
```xml
<ENVELOPE>
    <HEADER>
        <TALLYREQUEST>Export Data</TALLYREQUEST>
    </HEADER>
    <BODY>
        <EXPORTDATA>
            <REQUESTDESC>
                <REPORTNAME>List of Accounts</REPORTNAME>
                <STATICVARIABLES>
                    <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
                </STATICVARIABLES>
                <TDL>
                    <TDLMESSAGE>
                        <REPORT NAME="List of Accounts" ISMODIFY="Yes">
                            <FORMS>LedgerList</FORMS>
                        </REPORT>
                        <FORM NAME="LedgerList" ISMODIFY="Yes">
                            <TOPPARTS>LedgerListPart</TOPPARTS>
                        </FORM>
                        <PART NAME="LedgerListPart" ISMODIFY="Yes">
                            <TOPLINES>LedgerListLine</TOPLINES>
                            <REPEAT>LedgerListLine : LedgerListCollection</REPEAT>
                            <SCROLLED>Vertical</SCROLLED>
                        </PART>
                        <LINE NAME="LedgerListLine" ISMODIFY="Yes">
                            <LEFTFIELDS>LedgerName, LedgerParent, LedgerGroup</LEFTFIELDS>
                            <RIGHTFIELDS>LedgerOpeningBalance, LedgerClosingBalance</RIGHTFIELDS>
                        </LINE>
                        <FIELD NAME="LedgerName">
                            <SET>$Name</SET>
                        </FIELD>
                        <FIELD NAME="LedgerParent">
                            <SET>$Parent</SET>
                        </FIELD>
                        <COLLECTION NAME="LedgerListCollection">
                            <TYPE>Ledger</TYPE>
                            <BELONGSTO>Yes</BELONGSTO>
                        </COLLECTION>
                    </TDLMESSAGE>
                </TDL>
            </REQUESTDESC>
        </EXPORTDATA>
    </BODY>
</ENVELOPE>
```

## 📈 **Available Collections**

### **Core Tally Collections:**
1. **Ledger Collection**
   - Type: `Ledger`
   - Fields: `Name, Parent, Alias, OpeningBalance, ClosingBalance, Address, Phone, Email, IncomeTaxNumber, PartyGSTIN, GUID, CreatedOn, AlteredOn`

2. **Company Collection**
   - Type: `Company`
   - Fields: `Name, Address, Phone, Email, IncomeTaxNumber, GSTNo`

3. **Group Collection**
   - Type: `Group`
   - Fields: `Name, Parent, Alias`

4. **Voucher Collection**
   - Type: `Voucher`
   - Fields: `VoucherNumber, Date, VoucherType, Reference, Amount, Ledger`

5. **Item Collection**
   - Type: `StockItem`
   - Fields: `Name, Alias, BaseUnits, Category, OpeningBalance, ClosingBalance`

### **Customer-Specific Collections:**
```xml
<!-- Sundry Debtors (Customer) Collection -->
<COLLECTION NAME="CustomerCollection">
    <TYPE>Ledger</TYPE>
    <FILTER>BelongsTo: "Sundry Debtors"</FILTER>
    <FETCH>Name, Parent, Alias, OpeningBalance, ClosingBalance, Address, Phone, Mobile, Email, ContactPerson, Website, GSTNo, PanNo, CreditLimit, CreditPeriod</FETCH>
</COLLECTION>
```

## 🛠 **PHP Implementation**

### **ODBC Connection Class:**
```php
<?php
class TallyODBConnection {
    private $connection;
    private $server_url;
    private $company_name;
    
    public function __construct($server_url = 'http://localhost:9002', $company_name = '') {
        $this->server_url = $server_url;
        $this->company_name = $company_name;
    }
    
    public function connect() {
        // Method 1: HTTP XML (Recommended)
        if ($this->testHttpConnection()) {
            return true;
        }
        
        // Method 2: ODBC Fallback
        return $this->connectODBC();
    }
    
    private function testHttpConnection() {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->server_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode == 200 || $httpCode == 400); // 400 is normal for Tally without proper XML
    }
    
    private function connectODBC() {
        $dsn = "Driver={TallyODBC64_9002};Server=localhost:9002;Company={$this->company_name}";
        
        try {
            $this->connection = odbc_connect($dsn, '', '');
            return ($this->connection !== false);
        } catch (Exception $e) {
            error_log("ODBC Connection failed: " . $e->getMessage());
            return false;
        }
    }
}
```

## ⚙️ **Configuration Options**

### **Database Settings Table:**
```sql
-- Tally Integration Options
INSERT INTO `tbl_options` (`name`, `value`) VALUES
('tally_integration_enabled', '1'),
('tally_server_url', 'http://localhost:9002'),
('tally_company_name', ''),
('tally_auto_sync_invoices', '1'),
('tally_auto_sync_payments', '1'),
('tally_auto_sync_customers', '1'),
('tally_sync_on_create', '1'),
('tally_sync_on_update', '0');
```

### **Sync Log Table Structure:**
```sql
CREATE TABLE `tbl_tallysynclogs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sync_type` varchar(50) NOT NULL,
    `record_id` int(11) NOT NULL,
    `status` enum('success','error','pending') DEFAULT 'pending',
    `tally_response` text,
    `error_message` text,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sync_type` (`sync_type`),
    KEY `record_id` (`record_id`),
    KEY `status` (`status`)
);
```

## 🔧 **Data Field Mapping**

### **Customer Data Fields:**
```php
$customer_field_mapping = [
    // Tally Field => CRM Field
    'NAME' => 'company',
    'ADDRESS' => 'address',
    'PHONE' => 'phonenumber', 
    'MOBILE' => 'phonenumber',
    'EMAIL' => 'email',
    'CONTACTPERSON' => 'contact_firstname',
    'WEBSITE' => 'website',
    'GSTNO' => 'vat',
    'PANNO' => 'custom_fields',
    'OPENINGBALANCE' => 'custom_fields',
    'CLOSINGBALANCE' => 'custom_fields',
    'CREDITLIMIT' => 'custom_fields',
    'CREDITPERIOD' => 'custom_fields'
];
```

## 🚀 **Usage Examples**

### **1. Get All Customers:**
```php
$tally = new TallyDataRetriever('http://localhost:9002');
$response = $tally->get_all_ledgers_standard();
$customers = $tally_api->parse($response);
```

### **2. Sync Customer Data:**
```php
$log_id = $model->add_sync_log([
    'sync_type' => 'customer_import',
    'record_id' => 0,
    'status' => 'pending'
]);

try {
    $result = $tally_api->import_customers_from_tally();
    
    if ($result['success']) {
        $model->mark_sync_successful($log_id, json_encode($result));
    } else {
        $model->mark_sync_failed($log_id, $result['message']);
    }
} catch (Exception $e) {
    $model->mark_sync_failed($log_id, $e->getMessage());
}
```

## 🔍 **Troubleshooting Common Issues**

### **1. ODBC Connection Fails:**
- ✅ Ensure TallyPrime is running
- ✅ Check ODBC port (default: 9002)
- ✅ Verify company name matches exactly
- ✅ Use HTTP XML method as fallback

### **2. Data Collection Empty:**
- ✅ Verify customers exist in "Sundry Debtors" group
- ✅ Check date ranges in XML requests
- ✅ Ensure proper XML structure

### **3. Sync Failures:**
- ✅ Check sync logs table for detailed errors
- ✅ Verify database table structure
- ✅ Test connection before data operations

## 📊 **Integration Monitoring**

### **Dashboard Metrics:**
- Total synced records
- Success/failure rates by type
- Last sync times
- Recent activity (24 hours)
- Failed sync details

### **Available Reports:**
1. Sync Statistics Dashboard
2. Error Log Analysis  
3. Data Import/Export Reports
4. Customer Sync History

---

**💡 Recommendation:** Use HTTP XML Gateway method instead of direct ODBC for better reliability and error handling.

**🔄 Auto-Sync Features:**
- Real-time invoice sync
- Customer data synchronization
- Payment record updates
- Configurable sync schedules