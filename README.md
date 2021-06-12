# mayhewtech.com
Public website mayhewtech.com

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
