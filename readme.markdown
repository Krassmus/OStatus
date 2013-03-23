OStatus-adapter for Stud.IP-Blubber
===================================

Since the version 2.4 of the learn-management-software Stud.IP the software is 
capable of a social-network plugin called Blubber. Blubber supports course-messages, 
private and public discussions. This OStatus-plugin depends on Blubber and extends 
it for federating public messages to other Stud.IPs with this plugin or even other 
software-servers that are implementing the OStatus-protocol.

## Using this plugin

Install the plugin just the usual way (upload it manually or get it from the
plugin-marketplace) and activate it for all user-roles - even the not logged-in 
user-role "nobody"! No you can see the navigation Community -> External Contacts
which is the page to add someone as a friend by his/her webfinger-handle. So if 
your friend is having the username *krassmus* on the server http://develop.studip.de
the webfinger-id would be krassmus@develop.studip.de . This looks like an email-adress
but it does not necessarily be one. All you need is that at develop.studip.de the
OStatus-plugin is also installed and activated. Add *krassmus* as a friend and you 
can then get to his profile page (only Blubber-profile-stream) and you will see 
his postings in your global Blubber-stream. Once you added someone, it is all 
about to blubber with each other. OStatus-plugin will only take care of sending
the Blubber-messages to the other servers.

## OStatus-protocol

![OStatus-symbol](https://raw.github.com/Krassmus/OStatus/master/assets/ostatus.png)

The OStatus protocol consists of five layers that are well known protocol standards 
by themselves. OStatus combines them in a specially defined way and creates a
kind of wrapper protocol that lets different servers communicate with each other
about persons posting messages, comments and following each other. The five layers are:

* **[webfinger](http://code.google.com/p/webfinger/)**: To identify persons that have accounts on a different server, each user gets a webfinger-id that looks like an email-adress but doesn't need to be one. This adress is <username>@<servername>.<tld> and an example would be krassmus@develop.studip.de
* **[atom-feeds](http://www.atomenabled.org/developers/protocol/atom-protocol-spec.php)**: RSS/atom is a standard for sending messages and thus will be used in OStatus for highly readable feed-streams.
* **[activitystrea.ms](http://activitystrea.ms/specs/atom/1.0/)**: Most social-network sites are using activity-streams to define activities in the social network. Such an activity consists of actor, verb and an object (and sometimes a target) and is not limited to writing postings or comments. OStatus lets you federate any activities of the user.
* **[salmon](http://salmon-protocol.googlecode.com/svn/trunk/draft-panzer-salmon-00.html)**: Salmon is a workflow to distribute messages between servers by also verifying the identity of the person and is more secure than email. It reduces the traffic by sending data always to a central point upstream (like salmon swimming upstream).
* **[PubSubHubbub](http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html)**: For real-time news on your server it is nice to have other servers push notifications to your server, if there are any. PubSubHubbub is such a PuSH-server.

OStatus protocol in a whole and the workflow is described in the W3C-community: 
http://www.w3.org/community/ostatus/wiki/Main_Page

All together makes a nice workflow to distribute messages. One user is following 
another user, is getting the stream of global Blubber-messages, is able to comment 
on some Blubber, the other user is reading the comment in real-time and also all
users that are also following the user or having participated in the discussion. 
So we have a social network that is on multiple servers but feels like one big 
social network.

## OStatus-plugin as an interface

Federating postings is nice, but probably someone wants to build a plugin to 
federate bulletin-board entries or a file-repository. OStatus is highly extendable
because it relies on activity-streams. So most of the times you only need to define
a new tuple of verb and object-type (like post and bulletin-board-entry or save and file)
to start your federation. The only restriction for activities in OStatus is that the 
actor always has to be a person in your Stud.IP.

Try to add a NoticicationCenter::addObserver for the event "ActivityStreamProcesses"
in order to handle incoming activities in Stud.IP. Send your activities by using
OstatusUser::federateActivity and you're done - you don't need to bother about 
RSA-encryption and this kind of protocol-stuff. OStatus-plugin is doing the dirty
work your you.

## Requirements

This plugin is requiring only Stud.IP (2.4) with Blubber-plugin installed (which is
the default in 2.4 or later) and no PHP-extensions. RSA-signing of salmon-messages 
is done by the Crypt_RSA class, which part of this plugin. But 
having the openssl-extension activated in PHP can fasten some things up.

## License

The whole plugin combined with the Crypt_RSA class is having MIT license. Plugins
in Stud.IP have to be GPL 2 compatible, which is the case here. Usually I am 
licensing my plugin GPL 2 for consistency with the core-code, but in this case
MIT license can make it way easier for other PHP-programmers to reuse my classes.
And yes, I did not use or copy strings from APGL software.