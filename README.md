# mayhewtech.com
Public website mayhewtech.com

# local ubuntu dev environment
```php
sudo apt install php7.4-cli php7.4-curl php7.4-zip php-xml
```

## builds and deploys

Build & deployment should be done via local environment.

Generate a static version of the site for deployment to S3/CDN/IPFS.
<pre>
php createstatic.php
</pre>

Finally, deploy.
<pre>
php uploadeToS3.php
</pre>
