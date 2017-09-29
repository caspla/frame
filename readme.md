**Requirements**

- Apache 2.4 with activated mod_rewrite
- PHP 5.6 (or higher) 
- MySQL (optional)

**Installation**
1. Create VirtualHost that points to the "public" directory of the app
    
    ```apacheconfig
       <VirtualHost *:80>
         DocumentRoot "/var/www/myapp/public"
         ServerName frame.pascal.dev
         <Directory "/var/www/myapp/public">
             Options Indexes FollowSymLinks
             AllowOverride All
             Require all granted
         </Directory>
        </VirtualHost>
    ```
    
2. Configure database connection in "config.php"

    ```php
    [
        'database' => [
            'dsn' => 'mysql:host=localhost;dbname=myapp',
            'user' => 'myapp',
            'password' => 'supersecretpassword',
        ],
    ```
3. Point your browser to http://localhost (or any alias you have configured)
