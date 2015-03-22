This is currently only the bones of installation instructions.

# What you'll need #
  * A web server:
    * running PHP
    * with a database
    * and the ability to pipe mails to PHP scripts
    * I use Lunarpages.
  * A smattering of PHP knowledge

## Testing piped mail ##
Look in your control panel for the ability to forward mail addresses to '| some.php'.  A test script to get this working would be useful.  Note that this script must be set to executable, e.g. by your FTP client.

## Set up your database and config ##
Create your database and user before running the freeelist2db.sql script to set up the tables.  Copy the necessary details into config.php.

## Copy in PEAR and jQuery ##
This makes the DB calls much better.  You'll need to copy the [PEAR DB package](http://pear.php.net/package/DB) into your installation folder.  Likewise with [jQuery](http://docs.jquery.com/Downloading_jQuery) to get nice effects.

## Test processing ##
Send an email to trigger your mail forwarder to email.php.  This should appear in your database.  Otherwise you'll probably get a bounced mail.  Use error\_log to get this all working.

## Subscribe to free reuse list ##
I've tested this with Birmingham (UK) Freecycle and processed 10k posts over around 1000 days.  You'll need to tweak the processing for your own area codes.

## Tell me about it ##
I'd love to know if anyone is using this elsewhere!