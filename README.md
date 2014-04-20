# Forum plugin

This plugin adds a simple embeddable forum to [OctoberCMS](http://octobercms.com).

## Configuration

The forum does not require immediate configuation to operate. However the following options are available.

* Forum categories (Channels) can be managed via the System > Channels menu.
* Forum members can be managed via the User menu.

## Displaying a list of channels

The plugin includes a component channelList that should be used as the main page for your forum. Add the component to your page and render it with the component tag:

```php
{% component 'channelList' %}
```

You should tell this component about the other forum pages.

* **channelPage** - the page used for viewing an individual channel's topics.
* **topicPage** - the page used for viewing a discussion topic and posts.
* **memberPage** - the page used for viewing a forum user.

## Example page structure

#### forum/home.htm

```
title = "Forum"
url = "/forum"
layout = "default"

[channelList]
memberPage = "forum/member"
channelPage = "forum/channel"
topicPage = "forum/topic"
==

<h1>Forum</h1>
{% component 'channelList' %}
```

#### forum/channel.htm

```
title = "Forum"
url = "/forum/chan/:slug"
layout = "default"

[channel]
memberPage = "forum/member"
topicPage = "forum/topic"
==

<h1>Forum</h1>
{% component 'channel' %}
```

#### forum/topic.htm

```
title = "Forum"
url = "/forum/topic/:slug"
layout = "default"

[topic]
memberPage = "forum/member"
channelPage = "forum/channel"
==

<h1>Forum</h1>
{% component 'topic' %}
```

#### forum/member.htm

```
title = "Forum"
url = "/forum/member/:slug"
layout = "default"

[member]
channelPage = "forum/channel"
topicPage = "forum/topic"
==

<h1>Member</h1>
{% component 'member' %}
```