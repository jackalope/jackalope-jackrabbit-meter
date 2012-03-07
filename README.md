# jackalope-jackrabbit-meter

This is a little script to check, how many and what requests your app makes to jackrabbit

## Setup

You have to run the app on the same machine your jackrabbit access logs are.

* Put it somewhere on your filesystem (I put it in bin/jackalope-jackrabbit-meter in my sf2 app)
* copy `config.inc.php-dist` to `config.inc.php`
* Adjust the values to your needs (see the comments in the file)
* Run it with "php meter.php"
* See the results

## Example output

```
****
TEST for http://yourapp.lo/app.php/
HTTP Code         : 200
Body size         : 27631 bytes
Request time      : 409 ms
Total JR Requests : 22
 GET              : 10
 REPORT           : 7
 POST             : 3
 SEARCH           : 2
****
TEST for http://yourapp.lo/app.php/anotherpage
HTTP Code         : 200
Body size         : 26521 bytes
Request time      : 497 ms
Total JR Requests : 25
 SEARCH           : 3
 GET              : 12
 REPORT           : 7
 POST             : 3
****
```