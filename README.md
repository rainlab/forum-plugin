# Forum plugin

This plugin adds a simple embeddable forum to [October CMS](https://octobercms.com).

A video demonstration of this plugin can be seen here:
- https://vimeo.com/97088926

View this plugin on the October CMS marketplace:
- https://octobercms.com/plugin/rainlab-forum

## Configuration

The forum does not require immediate configuration to operate. However the following options are available.

* Forum categories (Channels) can be managed via the System > Channels menu.
* Forum members can be managed via the User menu.

## Displaying a List of Channels

The plugin includes a component forumChannels that should be used as the main page for your forum. Add the component to your page and render it with the component tag:

```twig
{% component 'forumChannels' %}
```

You should tell this component about the other forum pages.

* **channelPage** - the page used for viewing an individual channel's topics.
* **topicPage** - the page used for viewing a discussion topic and posts.
* **memberPage** - the page used for viewing a forum user.

### RSS Feed

Use the `forumRssFeed` component to display an RSS feed containing the latest blog posts. The following properties are supported:

* **channelFilter** - a channel slug to filter the topics by. If left blank, all topics are displayed.
* **topicsPerPage** - how many topics to display on the feed. The default value is 20.
* **forumPage** - path to the main forum page.
* **topicPage** - path to the topic details page.

The component can be used on any page, it will hijack the entire page cycle to display the feed in RSS format. The next example shows how to use it:

```
title = "RSS Feed"
url = "/forum/rss.xml"

[forumRssFeed]
forumPage = "forum"
topicPage = "forum/topic"
==
<!-- This markup will never be displayed -->
```

## Example Page Structure

#### forum/home.htm

```
title = "Forum"
url = "/forum"
layout = "default"

[forumChannels]
memberPage = "forum/member"
channelPage = "forum/channel"
topicPage = "forum/topic"
==

<h1>Forum</h1>
{% component 'forumChannels' %}
```

#### forum/channel.htm

```
title = "Forum"
url = "/forum/channel/:slug"
layout = "default"

[forumChannel]
memberPage = "forum/member"
topicPage = "forum/topic"
==

<h1>{{ channel.title }}</h1>
{% component 'forumChannel' %}
```

#### forum/topic.htm

```
title = "Forum"
url = "/forum/topic/:slug"
layout = "default"

[forumTopic]
memberPage = "forum/member"
channelPage = "forum/channel"
==

<h1>{{ topic.subject }}</h1>
{% component 'forumTopic' %}
```

#### forum/member.htm

```
title = "Forum"
url = "/forum/member/:slug"
layout = "default"

[forumMember]
channelPage = "forum/channel"
topicPage = "forum/topic"
==

<h1>{{ member.username }}</h1>
{% component 'forumMember' %}
```

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](LICENSE.md) for more details.
