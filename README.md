
PLEASE NOTE:

:	THIS PRELIMINARY PLUGIN IS IN FLUX AND IS SUBJECT TO CHANGE AT ANY TIME
:	At this time it is being published for internal use only. Please DO NOT RELY
:	upon it until this notice has been removed. (Which should be soon!)


#### Abstract

[SingleID](https://www.singleid.com) is a cloudless and data-decentralized identity provider with a privacy-preserving approach.
SingleID has the potential to become the “holy grail” of a single, widely deployable electronic ID, based on an open source protocol than runs on top of well known, widely studied, already existing systems like SSL and AES.
It provides a dramatically better user experience and at the same time a higher security than existing password­-based platforms for identifying and authenticating users as it does not require a central trusted third party. SingleID also automates form-filling with a single-­click based on a pre-aggregated master form stored in the user's smartphone app. 

#### Latest revision of this Readme

Status: Draft
Latest update: 2015-02-27

---

# How to request data from a Device 

The simple way to request data from a SingleID device is with a REST CALL to the SingleID Server.

This can be done with a plugin on the recipient system.

If for recipient system you are thinking to a website you can use the official SingleID "web plugin" button .
 
If for recipient system you intend something different like an ATM or a cash register you have only to follow the specific below.

For "web plugin" we intend an html code that had to be embedded on the form page as an iframe. This is the easiest way to install on each recipient system capable to render HTML page.

Alternative version of the plugin are welcome as they will follow the request scheme described in this document.


Another way to read the data stored in a SingleID Device is reading a QrCode.
The QrCode specs are written below.


```flow
st=>start: Recipient System
e=>end

op2=>operation: SingleID Web Plugin button
op3=>operation: Push Notification reach user's device
op4=>operation: User's Device will send the requested data Set
op5=>end: The Plugin will fill the webform
sub2=>subroutine: [REST CALL] Asking SingleID Server to forward request to corresponding device
cond=>condition: Enter your SingleID
cond2=>condition: User accept ?
io=>operation: You can always manually fill the Form

st->op2->cond
cond(no)->io(bottom)->e
cond(yes)->sub2->op3->cond2
cond2(no)->io->e
cond2(yes)->op4
op4->op5
```

## Plugin Installation

To use the **Official SingleID Web Plugin** on your site you must have in the same folder of you web form a file called plugin.php

The latest version of "plugin.php" is hosted, on [github](https://github.com/SingleID/web-plugin)

For convenience, in this White-Paper, we will call the web form page as form.php



### Requirements of the web plugin

form.php must have jquery
If you don't use jquery on that page you have to add with this line of code:


	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>



The next step is to insert the SingleId Button.

Place this code where you want to display the button:


	<iframe src="SingleID.php?op=init" width="290" height="80" frameborder="0"></iframe>


---

PLEASE NOTE:

:	You need also to create a folder called *userdata/* and make it writable from plugin.php
:	Please make sure that this folder is not browsable. For better security we recommended to create an empty index.html in this folder

---



Before testing you have to define 5 constants at the beginning of plugin.php


LOGO_URL

:	This is the full HTTP URL of your logo
	This logo will be displayed on the App when you will asking a request to the user.
	The logo will be showed inside a rectangle of 100x80 px so be careful with dimension and size in kb
	The logo should not have any trasparency.

example:

	define("LOGO_URL", "http://www.singleid.com/img/logonew.png");


---
Really Important:

:	- if the image is not reachable from web the push notification to the user will not be sent and will silenty fail !
	- The url MUST NOT be on https://
	- The url MUST NOT be over 50kb

---
	
SITE_NAME
 
:	This is the label that the user will see on the SingleID App when they receive a Push notification.
	
example:

	define("SITE_NAME", "test name from whitepaper");


*PLEASE NOTE* that this value must be shorter than 24 character

---

Requested_data

:	This is the type of data that you could ask to a device.
These are the allowed values and their meaning

	|String Value  | Meaning
	|---- | -------
	|1  | Personal Data / Company Data
	|1,2,3 | Personal, Billing and Shipping data
	|1,-2,3  | Personal, Billing and Shipping data ( Without credit card )
	|1,2,3,4 | Personal, Billing, Shipping and Identification data
	|1,-2,3,4 | Personal, Billing ( Without credit card ), Shipping and Identification data

example

	define("requested_data", "1,2,3");

	


---
**PLEASE NOTE:**

You are free to ask the set "1" with or without https.
	
In order to ask for the other set you MUST USE SSL for both form.php and plugin.php


---


A login request should appear in this way

![enter image description here](https://dl.dropboxusercontent.com/u/10636650/screenshot-markdown-SingleID/data-request.png)
	
	
billing_key

:	You are free to ask the set "1" with or without this field filled.
	In order to ask for the other set you MUST type in this field the billing_key.
	You can obtain a billing key on the website www.singleid.com 

The billing key is a secret value and had to keep hidden
 
admin contact
: 	Is an optional field. If isset we will sent to this email address an alert if something goes wrong between the plugin and our server.

We will send only 1 alert each 24 hours and only if the domain of the e-mail is the same of the domain where plugin.php is hosted.
	
	
## Latest step: Matching the input data with your existing form

If you look the code for an Array called **$arrTranslation** you will see the following lines:

	$arrTranslation['Pers_title'] 				= '';
	$arrTranslation['Pers_first_name'] 			= '';
	$arrTranslation['Pers_middle_name'] 			= '';
	$arrTranslation['Pers_last_name'] 			= '';
	$arrTranslation['Pers_birthdate'] 			= '';
	$arrTranslation['Pers_gender'] 				= '';
	$arrTranslation['Pers_postal_street_line_1'] 		= '';
	$arrTranslation['Pers_postal_street_line_2'] 		= '';
	$arrTranslation['Pers_postal_street_line_3'] 		= '';
	$arrTranslation['Pers_postal_city'] 			= '';
	$arrTranslation['Pers_postal_postalcode'] 		= '';
	$arrTranslation['Pers_postal_stateprov'] 		= '';
	$arrTranslation['Pers_postal_countrycode'] 		= '';
	$arrTranslation['Pers_telecom_fixed_phone'] 		= '';
	$arrTranslation['Pers_telecom_mobile_phone'] 		= '';
	$arrTranslation['Pers_first_email'] 			= '';
	$arrTranslation['Pers_skype'] 				= '';
	$arrTranslation['Pers_first_language'] 			= '';
	$arrTranslation['Pers_second_language'] 		= '';
	$arrTranslation['Pers_contact_preferred_mode'] 		= '';
	$arrTranslation['Pers_newsletter_agree'] 		= '';
	$arrTranslation['Pers_billing_first_name'] 		= '';
	$arrTranslation['Pers_billing_middle_name'] 		= '';
	$arrTranslation['Pers_billing_last_name'] 		= '';
	$arrTranslation['Pers_billing_telecom_fixed_phone'] 	= '';
	$arrTranslation['Pers_billing_telecom_mobile_phone']	= '';
	$arrTranslation['Pers_billing_email'] 			= '';
	$arrTranslation['Pers_billing_vat_id'] 			= '';
	$arrTranslation['Pers_billing_fiscalcode'] 		= '';
	$arrTranslation['Pers_invoice_required'] 		= '';
	$arrTranslation['Company_type'] 			= '';
	$arrTranslation['Company_name'] 			= '';
	$arrTranslation['Company_registration_number'] 		= '';
	$arrTranslation['Company_website'] 			= '';
	$arrTranslation['Comp_postal_street_line_1'] 		= '';
	$arrTranslation['Comp_postal_street_line_2'] 		= '';
	$arrTranslation['Comp_postal_street_line_3'] 		= '';
	$arrTranslation['Comp_postal_city'] 			= '';
	$arrTranslation['Comp_postal_postalcode'] 		= '';
	$arrTranslation['Comp_postal_stateprov'] 		= '';
	$arrTranslation['Comp_postal_countrycode'] 		= '';
	$arrTranslation['Comp_contact_title'] 			= '';
	$arrTranslation['Comp_contact_first_name'] 		= '';
	$arrTranslation['Comp_contact_middle_name'] 		= '';
	$arrTranslation['Comp_contact_last_name'] 		= '';
	$arrTranslation['Comp_contact_qualification'] 		= '';
	$arrTranslation['Comp_contact_department'] 		= '';
	$arrTranslation['Comp_contact_telecom_fixed_phone'] 	= '';
	$arrTranslation['Comp_contact_telecom_fax'] 		= '';
	$arrTranslation['Comp_contact_telecom_mobile_phone']	= '';
	$arrTranslation['Comp_contact_email'] 			= '';
	$arrTranslation['Comp_contact_skype'] 			= '';
	$arrTranslation['Comp_contact_language'] 		= '';
	$arrTranslation['Comp_contact_second_language'] 	= '';
	$arrTranslation['Comp_contact_preferred_mode'] 		= '';
	$arrTranslation['Comp_contact_newsletter_agree'] 	= '';
	$arrTranslation['Comp_billing_first_name'] 		= '';
	$arrTranslation['Comp_billing_middle_name'] 		= '';
	$arrTranslation['Comp_billing_last_name'] 		= '';
	$arrTranslation['Comp_billing_telecom_phone_number']	= '';
	$arrTranslation['Comp_billing_email'] 			= '';
	$arrTranslation['Comp_billing_vat_id'] 			= '';
	$arrTranslation['Comp_billing_fiscalcode'] 		= '';
	$arrTranslation['Ecom_payment_card_name'] 		= '';
	$arrTranslation['Ecom_payment_card_type'] 		= '';
	$arrTranslation['Ecom_payment_card_number'] 		= '';
	$arrTranslation['Ecom_payment_card_expdate_month'] 	= '';
	$arrTranslation['Ecom_payment_card_expdate_year'] 	= '';
	$arrTranslation['Ecom_payment_card_verification'] 	= '';
	$arrTranslation['Ecom_payment_card_visa_verified'] 	= '';
	$arrTranslation['Ecom_payment_mode'] 			= '';
	$arrTranslation['Ecom_shipto_postal_name_prefix'] 	= '';
	$arrTranslation['Ecom_shipto_postal_company_name'] 	= ''; // business
	$arrTranslation['Ecom_shipto_post_office_box'] 		= ''; // business
	$arrTranslation['Ecom_shipto_postal_name_first'] 	= '';
	$arrTranslation['Ecom_shipto_postal_name_middle'] 	= '';
	$arrTranslation['Ecom_shipto_postal_name_last'] 	= '';
	$arrTranslation['Ecom_shipto_postal_street_line1'] 	= '';
	$arrTranslation['Ecom_shipto_postal_street_line2'] 	= '';
	$arrTranslation['Ecom_shipto_postal_street_line3'] 	= '';
	$arrTranslation['Ecom_shipto_postal_floor'] 		= '';
	$arrTranslation['Ecom_shipto_postal_city'] 		= '';
	$arrTranslation['Ecom_shipto_postal_postalcode'] 	= '';
	$arrTranslation['Ecom_shipto_postal_stateprov'] 	= '';
	$arrTranslation['Ecom_shipto_postal_countrycode'] 	= '';
	$arrTranslation['Ecom_shipto_contact_phone'] 		= '';
	$arrTranslation['Ecom_shipto_phone_number_for_shipper'] = ''; // business
	$arrTranslation['Ecom_shipto_note'] 			= '';
	$arrTranslation['Ident_name_prefix'] 			= '';
	$arrTranslation['Ident_name_first'] 			= '';
	$arrTranslation['Ident_name_last'] 			= '';
	$arrTranslation['Ident_birthdate'] 			= '';
	$arrTranslation['Ident_gender'] 			= '';
	$arrTranslation['Ident_country_of_birth'] 		= '';
	$arrTranslation['Ident_country_of_citizenship'] 	= '';
	$arrTranslation['Ident_country_where_you_live'] 	= '';
	$arrTranslation['Ident_passport_number'] 		= '';
	$arrTranslation['Ident_passport_issuing_country'] 	= '';
	$arrTranslation['Ident_passport_issuance'] 		= '';
	$arrTranslation['Ident_passport_expiration'] 		= '';
	$arrTranslation['Ident_identity_card_number'] 		= '';
	$arrTranslation['Ident_identity_card_issued_by'] 	= '';
	$arrTranslation['Ident_identity_card_issuance'] 	= '';
	$arrTranslation['Ident_identity_card_expiration'] 	= '';
	$arrTranslation['Ident_driver_license_number'] 		= '';
	$arrTranslation['Ident_driver_license_issued_by'] 	= '';
	$arrTranslation['Ident_driver_license_issuance'] 	= '';
	$arrTranslation['Ident_driver_license_expiration'] 	= '';





The Key of the array are the fields name that will be sent from the App ( and that you cannot change ) . 

For each key you have to set as value the id of the input field present in your form.

Example #1

If you have a form like this


	<label>First Name</label>
	<input name="Name" id="id_name" type="text">
	<label>Last Name</label>
	<input name=lastname" id="id_lastname" type="text">
	...


You have to set the following values in $arrTranslations


	$arrTranslation['Pers_title'] 		= 'id_name';
	$arrTranslation['Pers_first_name'] 	= 'id_lastname';
	...


Of course you can change only the fields name in which you are interested.



 
## Plugin interaction with SingleID Server

When as user input in the plugin a 8 digit hex value  ( a Valid SingleID )

the plugin have to send, **server side**, a data request to the SingleID's Server over SSL


The data request must contain the following POST data

---

SingleID

:	The value typed in the plugin

UTID

:	A random value. must be an Md5 or a hex 32 char length

LOGO_URL

:	described above

SITE_NAME

:	described above

requested_data

:	already described

ssl 

:	could be 1 or 0 if the requested_data is = 1. otherwise must be 1

url_waiting_data
 
:	The url where the app had to send the data

ACTION_ID
:	must be "askfordata"


--- 

### Please note 

In the official plugin these step are already defined and they are correctly self-filled. They are here explained only to help you to understand each step.

--- 



### Server Reply

The Server, in case of correct data should respond as follow
 

	{
	  "SIDVer": "0.8.6",
	  "Reply": "ok",
	  "PopupTitle": "Good",
	  "Popup": "Your Request has been forwarded",
	  "PopupButtonLabel": "",
	  "PopupButtonUrl": "#"
	}



In case of error should response with following json



	{
	  "SIDVer": "0.8.6",
	  "Reply": "ko",
	  "PopupTitle": "Wrong",
	  "Popup": "I don't like your SingleID",
	  "PopupButtonLabel": "",
	  "PopupButtonUrl": "#"
	}


 
 
### What's next ?

The server must verify if the sender is allowed to send the request. 

There are formal check and sostantial check to do here.

If the sender is allowed a push notification will be sent to the SingleID device.





## Time to close the circle

Now the Device know:

1. Who is asking for data
2. Which type of data are looking for
3. The UTID ( or OTP as you prefer )
4. Where sent the values.


If the user decide to send the data, all the input name will be sent to the plugin.php located at the "url_waiting_data" with a single [POST CALL](http://en.wikipedia.org/wiki/POST_%28HTTP%29)


## How the plugin will fill the fields now

Since the plugin has been included as iframe, with a simple javascript (that is bundled with the web plugin) is now possible to assign the received value to the elements in the parent document.


	if(input_type == 'text')
	{
		parent.window.jQuery('#'+key).val(value);
	}
	else if(input_type == 'radio')
	{
		parent.window.jQuery('input[name="'+key+'"][value="'+value+'"]').prop("checked", true);
	}
	else if(input_type == 'checkbox')
	{
		parent.window.jQuery('input[class="'+key+'"][value="'+value+'"]').prop("checked", true);
	}
	else
	{
		parent.window.jQuery("#"+key).val(value);
	}

### Which data the plugin will receive from device 

When `requested_data_group` is set to 1 the plugin will receive the following data 

#### Personal profile

|requested_data_group  | Profile Type | POST var name | value
|---- | ------- | --- | --- | 
|1  | personal | UTID | [random one time token]
|1  | personal | which_set | `"personal"`
|1  | personal | Pers_title | user defined
|1  | personal | Pers_first_name | user defined
|1  | personal | Pers_middle_name | user defined
|1  | personal | Pers_birthdate | user defined
|1  | personal | Pers_gender | user defined
|1  | personal | Pers_postal_street_line_1 | user defined
|1  | personal | Pers_postal_street_line_2 | user defined
|1  | personal | Pers_postal_street_line_3 | user defined
|1  | personal | Pers_postal_city | user defined
|1  | personal | Pers_postal_postalcode | user defined
|1  | personal | Pers_postal_stateprov | user defined
|1  | personal | Pers_postal_countrycode | user defined
|1  | personal | Pers_telecom_fixed_phone | user defined
|1  | personal | Pers_telecom_mobile_phone | user defined
|1  | personal | Pers_first_email | user defined
|1  | personal | Pers_skype | user defined
|1  | personal | Pers_first_language | user defined
|1  | personal | Pers_second_language | user defined
|1  | personal | Pers_contact_preferred_mode | user defined
|1  | personal | Pers_newsletter_agree | user defined


#### Business profile

|requested_data_group  | Profile Type | POST var name | value
|---- | ------- | --- | --- | 
|1  | business | UTID | [random one time token]
|1  | business | which_set | `"business"`
|1  | business | Company_type | user defined
|1  | business | Company_name | user defined
|1  | business | Company_registration_number | user defined
|1  | business | Company_website | user defined
|1  | business | Comp_postal_street_line_1 | user defined
|1  | business | Comp_postal_street_line_2 | user defined
|1  | business | Comp_postal_street_line_3 | user defined
|1  | business | Comp_postal_city | user defined
|1  | business | Comp_postal_postalcode | user defined
|1  | business | Comp_postal_stateprov | user defined
|1  | business | Comp_postal_countrycode | user defined
|1  | business | Comp_contact_title | user defined
|1  | business | Comp_contact_first_name | user defined
|1  | business | Comp_contact_middle_name | user defined
|1  | business | Comp_contact_last_name | user defined
|1  | business | Comp_contact_qualification | user defined
|1  | business | Comp_contact_department | user defined
|1  | business | Comp_contact_telecom_fixed_phone | user defined
|1  | business | Comp_contact_telecom_fax | user defined
|1  | business | Comp_contact_telecom_mobile_phone | user defined
|1  | business | Comp_contact_email | user defined
|1  | personal | Comp_contact_skype | user defined
|1  | personal | Comp_contact_language | user defined
|1  | personal | Comp_contact_second_language | user defined
|1  | business | Comp_contact_preferred_mode | user defined
|1  | business | Comp_contact_newsletter_agree | user defined


                            

### Date format
Each date field is stored in SingleID with the [ISO8601](http://it.wikipedia.org/wiki/ISO_8601) format
For example the field Pers_birthdate will have the following value "YYYY-MM-DD"
But all the date input will be sent to recipient system also splitted in three var with the following suffix

	_day
	_month
	_year

So if you need the separated value you can read directly these var instead of split the first one

	// example
	Pers_birthdate_day
	Pers_birthdate_month
	Pers_birthdate_year
	

### Credit card format

The field called **Ecom_payment_card_number** is stored and sent to recipient system with only digits as [ISO7812](http://en.wikipedia.org/wiki/ISO/IEC_7812)

### Phone Number Format

Are allowed only digits and '+'


### Select List
 
**Be careful !**

The value on a select list will be assigned from Javascript row like this

	parent.window.jQuery("#"+key).val(value);

To avoid error you have to double check that the value that should be sent from the app are included in the possible values inside your select list!

The field that are stored as select list in the official app ( so with a fixed value ) are the following

|POST Name  | Possible values ( comma separated ) | More info
|---- | ------- | -----	
|which_set  | personal,business | Personal profile / Business profile
|Pers_title | Mr, Mrs | 
|Pers_gender  | M, F | Male or Female
|Pers_postal_countrycode| two uppercase char value  | [ISO 3166](http://www.theodora.com/country_digraphs.html)
|Pers_first_language| two lowercase char value  | [ISO 3166](http://www.theodora.com/country_digraphs.html)
|Pers_second_language| two lowercase char value  | [ISO 3166](http://www.theodora.com/country_digraphs.html)
| Pers_contact_preferred_mode | email, phone working hours, phone in the evening | Self-esplicative
| Pers_newsletter_agree | Y, N | Yes or No
| Ecom_payment_card_type | AMER, BANK, DC, DINE, DISC, JCB, MAST, NIKO, NSPK, SAIS, UC, UCAR, VISA, Vtron | American Express, Bankcard, DC, Diners Club, Discover, JCB, Mastercard, Nikos, PRO 100, Saison, UC, Ucard, Visa, Visa Electron
|Ecom_payment_mode | Credit card 1, Credit card 2, paypal | The user has selected the first credit card stored in his profile; or the second one; The user will pay the transaction with paypal so prepare the redirect | 

 



 



That's all folks

