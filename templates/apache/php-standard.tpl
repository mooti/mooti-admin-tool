<VirtualHost *:80>

    ServerName {{server_name}}
    DocumentRoot /mooti/repositories/{{repository_web_root}}

    <Directory /mooti/repositories/{{repository_web_root}}>
        Require all granted

        Options Indexes FollowSymlinks
        AllowOverride None

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ {{index_file}} [QSA,L]
    </Directory>

    LogLevel info
    ErrorLog /var/log/apache2/{{server_name}}.error.log

</VirtualHost>