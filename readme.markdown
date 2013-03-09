OStatus-adapter for Stud.IP-Blubber
===================================

Since the version 2.4 of the learn-management-software Stud.IP the software is 
capable of a social-network plugin called Blubber. Blubber supports course-messages, 
private and public discussions. This OStatus-plugin depends on Blubber and extends 
it for federating public messages to other Stud.IPs with this plugin or even other 
software-servers that are implementing the OStatus-protocol.

## OStatus

![OStatus-symbol](https://raw.github.com/Krassmus/OStatus/master/assets/ostatus.png)

The OStatus protocol consists of four layers that are long known protocol standards 
by themselves. OStatus combines them in a specially defined way and creates a
kind of wrapper protocol that lets different servers communicate with each other
about persons posting messages, comments and following each other. The five layers are:

* **[webfinger](http://code.google.com/p/webfinger/)**: To identify persons that have accounts on a different server, each user gets a webfinger-id that looks like an email-adress but doesn't need to be one. This adress is <username>@<servername>.<tld> and an example would be krassmus@develop.studip.de
* **[atom-feeds](http://www.atomenabled.org/developers/protocol/atom-protocol-spec.php)**: RSS/atom is a standard for sending messages and thus will be used in OStatus for highly readable feed-streams.
* **[activitystrea.ms](http://activitystrea.ms/specs/atom/1.0/)**: Most social-network sites are using activity-streams to define activities in the social network. Such an activity consists of actor, verb and an object (and sometimes a target) and is not limited to writing postings or comments. OStatus lets you federate any activity of the users. The OStatus-plugin wants other plugins in Stud.IP to federate any other activities as well.
* **[salmon](http://salmon-protocol.googlecode.com/svn/trunk/draft-panzer-salmon-00.html)**: Salmon is a workflow to distribute messages between servers by also verifying the identity of the person and is more secure than email. It also reduces the traffic by sending data always to a central point upstream (like salmon swimming upstream).
* **[PubSubHubbub](http://pubsubhubbub.googlecode.com/svn/trunk/pubsubhubbub-core-0.3.html)**: For real-time news on your server it is nice to have other servers push notifications to your server, if there are any. PubSubHubbub is such a PuSH-server.

OStatus protocol in a whole and the workflow is described in the W3C-community: 
http://www.w3.org/community/ostatus/wiki/Main_Page

All together makes a nice workflow to distribute messages. One user is following 
another user, is getting the stream of global Blubber-messages, is able to comment 
on some Blubber, the other user is reading the comment in real-time and also all
users that are also following the user or having participated in the discussion. 
So we have a social network that is on multiple servers but feels like one big 
social network.

## Requirements

This plugin is requiring only Stud.IP (2.4) with Blubber-plugin installed (which is
the default in 2.4 or later) and no PHP-extensions. RSA-signing of salmon-messages 
is done by the Crypt_RSA class, which is MIT licensed and part of this plugin. But 
having the openssl-extension activated in PHP can fasten some things up.