Junkyard DJ website – SMTP bundle notes
=======================================

This ZIP now includes the contact-form mail files:

- send-mail.php
- thank-you.html

What you still need to do before it will send mail
--------------------------------------------------
1) Download PHPMailer from:
   https://github.com/PHPMailer/PHPMailer

2) Upload the PHPMailer /src folder so your site structure looks like:

   /your-site
     index.html
     events.html
     schools.html
     hire.html
     contact.html
     thank-you.html
     send-mail.php
     style.css
     script.js
     /PHPMailer
       /src
         PHPMailer.php
         SMTP.php
         Exception.php

3) Open send-mail.php and replace these placeholders:
   - YOUR_EMAIL@YOURDOMAIN.CO.UK
   - YOUR_PASSWORD

4) Check your FastHosts / LiveMail SMTP settings:
   - host: smtp.livemail.co.uk
   - port: usually 587 (TLS) or sometimes 465 (SSL)
   - encryption: tls or ssl

5) Make sure contact.html posts to send-mail.php (already done in this ZIP)

Notes
-----
- The contact form includes a hidden honeypot anti-spam field.
- On success, users are redirected to thank-you.html
- On error, send-mail.php shows a simple error page.
