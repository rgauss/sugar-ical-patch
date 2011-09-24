SugarCRM iCal Patch
-------------------

**PREVIOUS USERS MUST CHANGE THE URL IN ICAL TO `ical_server.php` INSTEAD OF `vcal_server.php`**

###SugarCRM Setup:

Requires SugarCRM 6.2 or higher.

The patch is installed through the SugarCRM Module Loader and you must
'Clear Vardefs Data Cache' for the ProjectTask module from the Repair section
of admin as the patch adds a few fields that the SugarCRM team removed.
Once the patch is installed you may want to use the Studio to replace the
Date Due fields for ProjectTasks.

Each user should specify a **Publish Key** in the Calendar Options of their MyAccount
section in Sugar.  Note: this key will be transmitted in clear text and shown
on the screen in iCal and SugarCRM's interface so don't use your regular password.

###iCal Setup:

Once the key has been specified go to iCal and choose <strong>Subscribe</strong> from the
Calendar menu item.  Enter a URL similar to:

    http://YOURSERVER/sugar/ical_server.php?type=ics&email=YOUREMAIL&source=outlook&key=YOURKEY

replacing `YOURSERVER`, `YOUREMAIL`, and `YOURKEY` with their appropriate values.

You can also use **`user_name`** and your username in place of the email field.
(This is virtually the same as the URL given in the My Account section of
SugarCRM with the exception of type being ics instead of vfb)

Alternatively, you can just specify:

    http://YOURSERVER/sugar/ical_server.php?type=ics

and let iCal prompt you for your username and key (not your actual Sugar password).

###Number of Months:

The default number of months to fetch events from is +-2 but you can override that by
specifying num_months in the URL:

    http://YOURSERVER/sugar/ical_server.php?type=ics&num_months=4

####Encoding:

The default encoding will be UTF-8 but you can override that by specifying the encoding in the URL:

    http://YOURSERVER/sugar/ical_server.php?type=ics&encoding=iso-8859-1

####Charset:

The default charset will be UTF-8 but you can override that by specifying the charset in the URL:

    http://YOURSERVER/sugar/ical_server.php?type=ics&cal_charset=iso-8859-1

####HTTP Spec:

The default HTTP spec is 1.1 but you can override that by specifying the spec in the URL,
useful when deploying to some servers like nginx (which SugarCRM on demand uses):

    http://YOURSERVER/sugar/ical_server.php?type=ics&http_spec=1.0


####Changes for 0.8.6:

* Fix for date offset and ability to specify num_months in URL.


####Changes for 0.8.5:

* Fix for SugarCRM 6.2 changes affecting timezone DST start and stop.


####Changes for 0.8.4:

* Workaround for SugarCRM 6.2 bug [45177](http://www.sugarcrm.com/crm/support/bugs.html#issue_45177)


####Changes for 0.8.3:

* Compatible with SugarCRM 6.2
* Ability to hide calls with hide_calls=true
* http_spec parameter
* Output buffering fix provided by Dmitriy Trt
* No alerts for held events provided by Karl Ginter


####Changes for 0.8.2:

* Ability to override charset


####Changes for 0.8.1:

* Fix for timezone issues introduced in SugarCRM 6


####Changes for 0.8:

* Percent complete fixes for some clients like Lightning (Karl Ginter)
* Fix for due dates with time


####Changes for 0.7:

* Support for SugarCRM 6
* Timezone fix (Javier Diez [Escala IT](http://www.escalait.com"))


####Changes for 0.6:

* Upgrade safe
* Fixed for quote encoding
* Added ability to specify encoding


If you find this extension useful please consider making a donation to encourage its development in the future:

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VNSGPCJPR5NN4"><img border="0" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" /></a>

