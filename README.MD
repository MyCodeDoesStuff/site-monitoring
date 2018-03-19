#Site Monitoring Application
##Configuration and Installation
1. Clone the repository on the web server on which you'd like the application to reside.
2. Update includes/db.php to set the host, database name, username, and password to be used with your MySQL database.
3. Update includes/config.php to set the name of the client, the URL you wish to test, the keyword or phrase that should be present in the server's response to confirm a valid test along wihh the mobile phone number of the recipient of failed test notifications and your TextBelt API key.
4. Execute tests.sql to create the necessary table schema in your MySQL database.
5. Configure a cron job to execute the monitor.php script at an interval of your choosing.