=== Pending Email SMTP ===
Contributors: binmaocom
Tags: mail, smtp, phpmailer, pending email, mailing queue, wp_mail, email, fast email smtp, delay email
Donate link: https://www.paypal.com/paypalme/binmaocom
Requires at least: 5.7.0
Tested up to: 5.7.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add emails to a mailing queue instead of sending immediately to speed up sending forms for the website visitor and lower server load.

== Description ==
This plugin adds emails to a mailing queue instead of sending immediately. This speeds up sending forms for the website visitor and lowers the server load.
Emails are stored in database which are deleted after emails are sent.

You can send all outgoing emails via an SMTP server or (the WordPress standard) PHP function [mail](http://php.net/manual/en/function.mail.php), and either use [wp_cron](https://codex.wordpress.org/Function_Reference/wp_cron) or a cronjob (if your server/hoster supports this) to process the queue.

Plugin requires PHP 5.4 or above.

== Installation ==
1. Upload the files to the `/wp-content/plugins/pending_wp_mail/` directory
2. Activate the \"Pending Email SMTP\" plugin through the \"Plugins\" admin page in WordPress
3. Please turn on your cronjob to sure the \"Pending Email SMTP\" plugin can send the email

* First commit of the plugin