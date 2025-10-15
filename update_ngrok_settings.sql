-- Update Tally Integration settings for ngrok URL
-- Replace the server URL with your ngrok URL

-- Update server URL to ngrok
INSERT INTO tbloptions (name, value) VALUES ('tally_server_url', 'https://josue-considerate-brad.ngrok-free.dev') 
ON DUPLICATE KEY UPDATE value = 'https://josue-considerate-brad.ngrok-free.dev';

-- Enable integration
INSERT INTO tbloptions (name, value) VALUES ('tally_integration_enabled', '1') 
ON DUPLICATE KEY UPDATE value = '1';

-- Remove old server_port option if it exists
DELETE FROM tbloptions WHERE name = 'tally_server_port';

-- Set default sync options
INSERT INTO tbloptions (name, value) VALUES ('tally_auto_sync_customers', '1') ON DUPLICATE KEY UPDATE value = '1';
INSERT INTO tbloptions (name, value) VALUES ('tally_auto_sync_invoices', '1') ON DUPLICATE KEY UPDATE value = '1';
INSERT INTO tbloptions (name, value) VALUES ('tally_auto_sync_payments', '1') ON DUPLICATE KEY UPDATE value = '1';
INSERT INTO tbloptions (name, value) VALUES ('tally_sync_on_create', '1') ON DUPLICATE KEY UPDATE value = '1';
INSERT INTO tbloptions (name, value) VALUES ('tally_sync_on_update', '0') ON DUPLICATE KEY UPDATE value = '0';

-- Show current tally settings
SELECT name, value FROM tbloptions WHERE name LIKE 'tally_%' ORDER BY name;


ngrok config add-authtoken 335CImssxfLow7T6hLT8sdWaUu0_48NvVJdpNzGGHuEHSz7ve

ngrok http 9002

user - accoutcrm
pwd - a#Af78ga4n&RghCb
db - admin_crm

https://livecrmsoftware.com/