
# What is SingleID

[SingleID](https://www.singleid.com) is a patent-pending identity management and authentication platform providing users with a smartphone app to organize their personal data that can be accessed by any third party needing to identify that user by being furnished with their SingleID. 

In a way SingleID is a mobile data wallet that pre-aggregates different kinds of data – personal data, billing data, shipping data, identification data (passport, driver’s license), authentication data such as digital signatures etc., membership data, contract data, etc. – so that they can be released with a single click from your smartphone upon request by a third party.

And all of this is done with a 'zero knowledge' architecture and state-of-the art encryption, observing the highest privacy and security standards. SingleID is a distributed platform and thus no database of sensitive personal data is being built up in the cloud.

Decentralization of data breaks the old paradigm based on trusted third party.

The data are aggregated, managed and sent in the manner described herein.

This document also describes how to apply this information and the ways in which a device "SingleID compliant" must communicate in response to requests.

Data can be requested from any recipient system that know the SingleID of a device and that are connected to the Internet.

The data can be saved in the device, manually by the user or by reading a code QrCode whose structure is described in this document

## SingleID sequence chart

The following diagram demonstrate how SingleID is not only a digital wallet and a form filling software, but is also a two step authenticator with a disposable one time password and without storing any personal data on "third party server".


### Requirements
1. The user already has the App SingleID with a profile saved
2. The recipient system has the plugin Installed as described in this White-Paper



### Definition and meaning
Term         | Meaning
----------------- | -------
Recipient System  | Tipically a web site with the official web plugin installed
SingleID Server   | Any server owned from SingleID Inc. that is compliant with the Server Protocol explained below 
data request      | The type of data that the recipient system is asking for ( personal , billing, shipping ... )
User's Device  | A SingleID compliant device


### Diagram


```sequence
Recipient System-> SingleID Server: Forward this data request to the User's Device
Note over SingleID Server: various check to prevent abuses and forward to
SingleID Server-> SingleID User's Device: A Push notification is delivered
Note over SingleID User's Device: Do you want to send data ?
SingleID User's Device-> User decision: The user could accept or decline
Note over User decision: If the user accept...
User decision--> Recipient System: The requested data are sent directly from the device to the recipient system.
```

### Please note 
1. A disposable, One Time Password is randomly generated at the beginning from the Recipient system ( UTID ) and is trasmitted to the SingleID Server, that will forward also the UTID to the user's device.
2. No personal data has been transmitted over the SingleID Server
3. The SingleID server doesn't know also if the user accept or decline to send the data to the recipient system
4. The recipient System will receive not only the personal data from the user's device, but also the UTID. This is sufficient to say that the user's device where the data come from is really in the hands of the person who start the request



---

## Server Protocol

This paragraph is about the server protocol version until version 1.0

The official server url is https://app.singleid.com

---

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED",  "MAY", and "OPTIONAL" in this document are to be interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt) .

---


## Allowed interaction with SingleID Server

There are only 6 allowed action for exchange information with the SingleID Server.

5 are from a user's device

:	1. getnewSingleID
	2. bringbackmypwd
	3. tellmemore
	4. updatepushID
	5. setmyrecoveryemail


One is from the recipient system

:	1. askfordata


## What happen in a User's Device

```flow
st=>start: App start
e=>end
op1=>operation: Looking for PushID
op3=>operation: Now can read/write encrypted data inside
op4=>operation: Check Server for queued request with "tellmemore"

sub2=>operation: Retrieve password from the server with "bringbackmypwd"
cond=>condition: Is there a SingleID and a TOKEN ?
cond2=>condition: Accept
io=>operation: execute a "getnewSingleID"

st->cond
cond(no)->io(bottom)->op3
cond(yes)->sub2->op3->op4->e
```
# Understand each interaction with the Server

Each step below reported are already coded in the official App. This White-Paper is only to explain how the App Works and how is simple and powerful the logic behind.

The official App is available on the [Android play store](https://play.google.com/store/apps/details?id=com.singleid.wapp)  with the name "SingleID".
The App is also available for [Windows Phone 8](http://www.windowsphone.com/it-it/store/app/singleid/2b9570c4-09eb-45db-8bab-37ba3cab9c06) on the Microsoft Store with the same name.

When you open the App for the first time, the app do these steps:

 - Create a random string of 32 hexadecimal char called TOKEN
 - Retrieve the PushID from the own Push Server ( Android, iOs, WP8 )
 - Send these data to the SingleID Server asking for a new, and never used before, SingleID ( an 8 digit hexadecimal string )
 - Wait for a Push notification with a login request
 
## First Action: getnewSingleID
The first interaction that the app must do with the SingleID server is the getnewSingleID

The App have to send the following POST DATA, over SSL, to the SingleID Server

PLATFORM

: 	Permitted values are:
	Value  | Details
	---- | -------
	GCM  | for Android device
	APNS | for iOs device
	WM8  | for Windows Phone 8 device

REGISTRATION_ID

:	Is the "Push id" of the Device
	Value  | Details
	---- | -------
	RegistrationId  | for Android device
	AppleId | for iOs device
	XXX  | for Windows Phone 8 device

	
TOKEN

:   Must be a random string of 32chars hexadecimal created from the app
Tecnically speaking this is the unique value that could identify a device.
**We do not rely on UUID for privacy reason.**

DETECTED_DEVICE_LANGUAGE

:	2 char code (from [iso 639 standard](http://en.wikipedia.org/wiki/ISO_639))
allowed value are: it, en, bg, de.
 
APP_VERSION

:	A value between 0.9 and 1.2 
	
DEVICE_TYPE

:	Permitted values are:
	|Value  | Details
	|---- | -------
	|smartphone  | for stat purposes only
	|tablet | for stat purposes only
	|wearable  | for stat purposes only
	|testing | for testing purposes
	
ACTION_ID

:	Permitted value is:
	getnewSingleID
	


	
### Response to a correct getnewSingleID request

The response of the server, in case of a correct request, must be a Json string with the following values


	{
	"SIDVer": "0.8.2",
	"Reply": "ok",
	"SingleID": "b1625ebe",
	"Password": "b6f2a6539c290deb6c0b4a11707216c6",
	"Recovery Key": "a73ca9905a5d600095da15f862a82a22",
	"PopupTitle": "Welcome",
	"Popup": "Now you have an unique SingleID",
	"PopupButtonLabel": "Enjoy",
	"PopupButtonUrl": "#"
	}


SIDVer
:	The Software Server version.
	
Reply
:	The status of the request. Could be 'ok' or 'ko'
	
SingleID
:	The SingleID that the Server has associated to the TOKEN sent before. Tecnically we identify a device from his TOKEN
	
Password
:	All the personal data stored inside the device must be encrypted with AES 256 with this password. The password must not be saved into the device.

Recovery Key
:	This is a secret value that had to be stored inside the device for future purposes
	
Popup(*)
:	Are string values that could be displayed to the user from the App
	
---

	
## Subsequent requests from an already registered device
	
When the App start, and already have a SingleID and a TOKEN, the App have to request back from the server the password to be able to decrypt the data stored inside.

In order to be SingleID compliant the App must sent the following POST DATA, over SSL, to the SingleID Server


|Var Name  | Value
|---- | -------
|ACTION_ID  | bringbackmypwd
|SingleID | `$SingleID`
|TOKEN  | `$TOKEN`
|DETECTED_DEVICE_LANGUAGE | Used for stat purpose only ( two char code as [iso 639 standard](http://en.wikipedia.org/wiki/ISO_639))
|APP_VERSION  | Used for stat purpose only



### Response to a correct bringbackmypwd request

The response of the server in case of a correct request will be a Json string like the following

	{
	"SIDVer": "0.8.2",
	"Reply": "ok",
	"Password": "a3f2a6539a290deb6c0b4a11707216c7",
	"PopupTitle": "",
	"Popup": "",
	"PopupButtonLabel": "",
	"PopupButtonUrl": ""
	}


SIDVer
:	the Software Server version.
	
Reply
:	the status of the request. Could be 'ok' or 'ko'
	
Password
:	All the data stored inside the device must be encrypted with AES 256 with this password. The password must not be saved into the device.

Popup(*)
:	Are string values that could be displayed to the user from the App

--- 

## Check for queued Request

When receiving a Push Notification, the device must check the SingleID Server for queued authentication request

The payload of a push notification is too small to contain all the data so when a push notification reach the device, the device will make a REST CALL to the server with the following value:

|Var Name  | Value
|---- | -------
|ACTION_ID  | tellmemore
|SingleID | `$SingleID`
|PASSWORD  | `$PASSWORD`
|DETECTED_DEVICE_LANGUAGE | Used for stat purpose only ( two char code as ISO  [iso 639 standard](http://en.wikipedia.org/wiki/ISO_639))
|APP_VERSION  | Used for stat purpose only





### Response with only one pending request
     
	[
	{
	"SingleID": "30d37d54",
	"Date": "2014-10-23T15:24:37+01:00",
	"Name": "BetaTesting Inc.",
	"Logo_url": "http:\/\/www.singleid.com\/img\/logonew.png",
	"url_waiting_data": "http:\/\/www.singleid.com\/dv1\/plugin.php",
	"requested_data_group": "1",
	"ssl": "0",
	"UTID": "e6b9973ea39ee28a88b687afaa1835c9"
	}
	]

---

SingleID
:	The device SingleID. Must be included for future purposes.
	
Date
: 	Date of the request ( [ISO 8601 standard](http://en.wikipedia.org/wiki/ISO_8601) )
	
Name
:	Name of the recipient system. Should be displayed to the user.
	
Logo_url
:	Logo of the recipient system. Should be displayed to the user.

url_waiting_data
:	The url where the app must send the data if the user accepts.
	
requested_data_group
:	Which type of data the recipient system is asking for. The user must not change this value.

:	Permitted values are:
	|Value  | Meaning
	|---- | -------
	|1  | Personal Data / Company Data
	|1,2,3 | Personal, Billing and Shipping data
	|1,-2,3  | Personal, Billing and Shipping data ( Without credit card )
	|1,2,3,4 | Personal, Billing, Shipping and Identification data
	|1,-2,3,4 | Personal, Billing ( Without credit card ), Shipping and Identification data
	
ssl
: 	Must be 0 for http or 1 for https ( related to url_waiting_data )

UTID
:	Acronym for Unique Transaction ID. Must be a hexadecimal 32char string. Is generated randomly from the recipient system at the beginning of the request.
:	The value must be sent with the personal data to the `url_waiting_data`
	
--- 
	
	
	

### Response with more than one pending request


	[
	{
	"SingleID": "30d37d54",
	"Date": "2014-10-23T15:24:37+01:00",
	"Name": "BetaTesting Inc.",
	"Logo_url": "http:\/\/www.singleid.com\/img\/logonew.png",
	"url_waiting_data": "http:\/\/www.singleid.com\/dv1\/plugin.php",
	"requested_data_group": "1",
	"ssl": "0",
	"UTID": "e6b9973ea39ee28a88b687ffaa1835c9"
	},
	{
	"SingleID": "30d37d54",
	"Date": "2014-10-23T15:28:56+01:00",
	"Name": "BetaTesting Inc.",
	"Logo_url": "http:\/\/www.singleid.com\/img\/logonew.png",
	"url_waiting_data": "http:\/\/www.singleid.com\/dv1\/plugin.php",
	"requested_data_group": "1",
	"ssl": "0",
	"UTID": "f7b0666f7a2354a52018c73682648222"
	},
	{
	"SingleID": "30d37d54",
	"Date": "2014-10-23T15:28:59+01:00",
	"Name": "BetaTesting Inc.",
	"Logo_url": "http:\/\/www.singleid.com\/img\/logonew.png",
	"url_waiting_data": "http:\/\/www.singleid.com\/dv1\/plugin.php",
	"requested_data_group": "1",
	"ssl": "0",
	"UTID": "aaa0666f7a2350a47018c3131d5fe821"
	}
	]
	


### Response without queued request
                    
	{
	"SIDVer":"0.8.2",
	"Reply":"ok",
	"PopupTitle":"",
	"Popup":"",
	"PopupButtonLabel":"",
	"PopupButtonUrl":""
	}
                    
### Response in case of error
      
	{
	"SIDVer":"0.8.2",
	"Reply":"ko",
	"PopupTitle":"",
	"Popup":"Something goes wrong...",
	"PopupButtonLabel":"",
	"PopupButtonUrl":""
	}
                    


## Update Push Notification ID ( for iOs e WP8 device only )

Each App installed on a smartphone has an internal "PushID". This means that if you want remotely wake app a specific app, you can gently ask to Apple|Google|Microsoft to forward a push notification to a specific App. 

On Android the "PushID" related to an App on a device doens't change also if you remove the App and reinstall it.

On other platform ( Apple and Microsoft ) the "PushID" could change.

In these cases, in order to receive push Notification,, the App should update the device information in the following way. 
The App must sent the following POST DATA, over SSL, to the SingleID Server


|Var Name  | Value
|---- | -------
|ACTION_ID  | updatepushID
|SingleID | `$SingleID`
|PASSWORD  | `$PASSWORD`
|DETECTED_DEVICE_LANGUAGE | Used for stat purpose only ( two char code as ISO  [iso 639 standard](http://en.wikipedia.org/wiki/ISO_639))
|APP_VERSION  | Used for stat purpose only
|NEW_REGISTRATION_ID | The new pushid to use

**Note:** iOS device tokens must be 64 hexadecimal characters



### Response to a correct updatepushID request

	{
	"SIDVer":"0.8.2",
	"Reply":"ok",
	"PopupTitle":"",
	"Popup":"",
	"PopupButtonLabel":"",
	"PopupButtonUrl":""
	}

### Response in case of wrong request
      
	{
	"SIDVer":"0.8.2",
	"Reply":"ko",
	"PopupTitle":"Ops!",
	"Popup":"Something goes wrong...",
	"PopupButtonLabel":"",
	"PopupButtonUrl":""
	}
	


## How enable the 'Remote Lock' possibility

---
A user's device could be remotely locked only if a user execute this step **before the loss** of the device.

---


The App must sent the following POST DATA, over SSL, to the SingleID Server

|Var Name  | Value
|---- | -------
|ACTION_ID  | setmyrecoveryemail
|SingleID | `$SingleID`
|PASSWORD  | `$PASSWORD`
|DETECTED_DEVICE_LANGUAGE | Used for stat purpose only ( two char code as [iso 639 standard](http://en.wikipedia.org/wiki/ISO_639))
|APP_VERSION  | Used for stat purpose only
|Salt  | A Random hexadecimal string of 16 char created from the App
|HashedEmail  | The Md5 hash of (Salt + Email). In this way we do not store the email of the user but only an hash of the email. So we cannot contact the user but the user could remotely lock the device if he send us an email from this email.




### Response to a correct setmyrecoveryemail request

	
	{
	"SIDVer": "0.8.2",
	"Reply": "ok",
	"PopupTitle": "Email saved",
	"Popup": "If you want to remotely lock this SingleID send us an email from this address",
	"PopupButtonLabel": "How to",
	"PopupButtonUrl": "http:\/\/www.singleid.com\/faq\/"
	}
	
### Response in case of error
      
	{
	"SIDVer":"0.8.2",
	"Reply":"ko",
	"PopupTitle":"Ops!",
	"Popup":"Something goes wrong...",
	"PopupButtonLabel":"",
	"PopupButtonUrl":""
	}
	
	
---


# How to request data from a Device 

The simple way to request data from a SingleID device is with a REST CALL to the SingleID Server.

This can be done easily embedding a plugin on the recipient system.

If for recipient system you are thinking of a website you can use the official SingleID "web plugin" button.
 
If for recipient system you intend something different like an ATM or a cash register you have only to follow the specific below.

For "web plugin" we intend an html code that had to be embedded on the form page as an iframe. This is the easiest way to install on each recipient system capable to render HTML page.

Alternative version of the plugin are welcome as they will follow the request scheme described in this document.

```flow
st=>start: Recipient System
e=>end
op1=>operation: Web Form Page
op2=>operation: SingleID Web Plugin button
op3=>operation: Push Notification reach user's device
op4=>operation: User's Device will send the requested data Set
op5=>end: The Plugin will fill the webform
sub2=>subroutine: [REST CALL] Asking SingleID Server to forward request to corresponding device
cond=>condition: Enter your SingleID
cond2=>condition: User accept ?
io=>operation: You can always manually fill the Form

st->op1->op2->cond
cond(no)->io(bottom)->e
cond(yes)->sub2->op3->cond2
cond2(no)->io->e
cond2(yes)->op4
op4->op5
```

# Plugin Installation

To use the **Official SingleID Web Plugin** on your site you must have in the same folder of you web form a file called plugin.php

The latest version of "plugin.php" is hosted, with all dependencies, on github [here]() ( **TODO** )

For convenience, in this White-Paper, we will call the web form page as form.php



## Requirements of the web plugin

form.php must have jquery
If you don't use jquery on that page you have to add with this line of code:


	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>



The next step is to insert the SingleId Button.

Place this code where you want to display the button:


	<iframe src="plugin.php?op=init" width="220" height="80" frameborder="0"></iframe>


---

PLEASE NOTE:

:	You need also to create a folder called *userdata/* and make it writable from plugin.php
:	Please make sure that this folder is not browsable. For better security we suggest you to create an empty index.html in this folder

---



Before testing you have to define 5 constants at the beginning of plugin.php


LOGO_URL

:	This is the full HTTP URL of your logo
	This logo will be displayed on the App when you will asking a request to the user.
	The logo will be showed inside a rectangle of 100x80 px so be careful with dimension and size in kb
	The logo should not be with alpha channel.

example:

	define("LOGO_URL", "http://www.singleid.com/img/logonew.png");


---
PLEASE NOTE:

:	- if the image is not reachable from web the push notification to the user will not be sent !
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

	|Value  | Meaning
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

## Date format

Please note that all the date are stored in SingleID with the [ISO8601](http://it.wikipedia.org/wiki/ISO_8601) format
For example the field Pers_birthdate will have the following value "YYYY-MM-DD"
But all the date input will be sent also splitted in three var with the following suffix
_day
_month
_year

So if you need the separated value you can read directly these var instead of split the first one


 ###### TODO SELECT LIST
 
 ###### TODO CHECKBOX
 
 ###### TODO OPTION BUTTON
 
 
 
 ###### TODO if you need a password value we are able to generate a random one in order to pass your validation rules
 but we need to check the following three cases
 
 ###### TODO maybe the form where the user choice a password togheter with personal data are not good for SingleID
 TO INVESTIGATE
 
 1) First visit of a *new* user with SingleID
		You can create an account with the random password
		
 2) Subsequent visit of the same user with SingleID
	If the user is already present you MUST skip the password check because it's random and surely different from the previous one
	
 3) First visit of a *old* user with SingleID ( an user whose email is already present )
 
	You have to merge the account only after a click on the confirmation email

 
 

 
## Plugin interaction with SingleID Server

When as user input in the plugin a 8 digit hex value  ( a Valid SingleID )

the plugin, **server side**, must send a request to the SingleID's Server over SSL

Note again and again 
:	must not be an ajax request from the client browser but a server side request

The request must contain the following POST data

---

SingleID

:	The value typed in the plugin

UTID

:	A random value. must be an Md5 or a hex 32 char length

 - LOGO_URL
	* described above
 - SITE_NAME
	* described above
 - requested_data
	* already described
 - ssl 
	* could be 1 or 0 if the requested_data is = 1. otherwise must be 1
 - url_waiting_data 
	* The url where the app had to send the data
 - ACTION_ID
	* must be "askfordata"


--- 

### Please note 

In the official plugin these step are already defined and they are correctly self-filled

--- 



### Server Reply

The Server, in case of correct data should respond as follow
 
	[TODO] 



In case of error should response with following json

	[TODO]



 
 
### What's next ?

The server must verify if the sender is allowed to send the request. 

There are formal check and sostantial check to do here.

If the sender is allowed a push notification will be sent to the SingleID device.







## The App knows everything now

Now the App know who is asking for data, which type of data are looking for and  where sent the values.


If the user decide to send the data, all the input name will be sent to the plugin.php located at the "url_waiting_data"


## How the plugin will fill the fields now



That's all folks
