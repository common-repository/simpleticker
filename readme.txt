=== SimpleTicker ===
Contributors: Michael Bartel
Donate link: Plugin URI: http://simpleticker.mbartel.de/
Tags: news,ticker,newsticker,textticker,text,fader,scroller,facebook
Requires at least: 2.6
Tested up to: 3.2.1
Stable tag: 0.95

== Description ==
A simple ticker plugin for wordpress. It supports multiple tickers. You can define an update interval
in minutes in which the client updates it's message list from the server. This update request includes
new messages, which have been posted until the last update. You can also specify the amount of messages that
the client fades through and the time each message stays on the screen. Each message is stored with an
creation timestamp. You can tell the ticker only to show messages not older than a defined number of minutes.
If there are no messages to display, then the ticker turns itself invisible.

Every ticker has it's own RSS Feed, which can be received by either given it's ID or name.

If you want to use the SimpleTicker data from an other application such as an iPhone or Android App, you can
get all ticker data and messages via an JSON based API. It is also possible to add and delete messages
with the JSON API. Your application will need a password for each ticker, if it want's to add or delete messages.

== Copyright ==
Wordpress - Plugin "SimpleTicker"
(c) 2013 Michael Bartel, MIT/X11-license
eMail: Michael.Bartel@gmx.net

== History ==
Version 0.95
 - Refactoring

Version 0.9
 - Bugfixing

Version 0.8
 - Improvements to the Android App

Version 0.7
 - Android App improvments

Version 0.6
 - Android App and Bug fixes

Version 0.5
 - added RSS Feed

Version 0.4
 - added Template-Engine and XML API

Version 0.3
 - added JSON API

Version 0.2
 - added auto-hide

Version 0.1
 - first version V3.1

== Installation ==
Unzip the plugin into your wordpress wp-content/plugins/ folder.

You will find the plugin configuration site in your wordpress administration plugins section.

== Use Ticker ==
To use the ticker on your wordpress pages or articles copy the following text into the page. After that replace #id# with the id of your ticker.

[simpleticker id=#id#]

Example for ticker 1 (with id 1): [simpleticker id=1]

== RSS Feed ==
You will receive the content of the RSS Feed for a specific ticker when you call SimpleTicker.php from your plugin folder (URL) and append '?action=rssFeed&id=1'. Instead of id=1 you can use name=Tickername.

== APIs ==
All APIs are handled with GET paramters. The 'action' parameter specifys which function you want to call.

== JSON API ==
Instead of the using the id parameter with the tickers ID, you can use the name parameter with it's name. The JSON API provides the following functionalities:

* jsonGetTickerList - Returns a full list of all available tickers containing the tickers id and name.
* jsonGetTickerMessages - Returns a list with the last 50 messages of a ticker. You have to specify the ticker by giving it's ID in the parameter 'id'. The list contains the message id, the message itself and the createdOn timestamp.
* jsonManageTicker - You need a password to call the action. All further parameters are given in a BASE64 encoded encrypted JSON string provided as GET parameter named 'data'. You have to set the 'id' parameter as above to define a ticker. The JSON string contains an action attribute which can either be 'addMessage' or 'removeMessage'. The 'addMessage' actions takes an additional 'message' attribute containing the new message and the 'removeMessage' action takes an 'id' attribute.
