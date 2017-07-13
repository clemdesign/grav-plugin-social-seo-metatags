# Social & SEO Meta-Tags Plugin

The **Social & SEO Meta-Tags** Plugin is for [Grav CMS](http://github.com/getgrav/grav).

## Description

Add and manage the SEO meta-tags:
- Description
- Robots
- Keywords

Add all Meta-Tags that need Facebook Open Graph, and Twitter Cards.

Note: `keyword` meta-tag is deprecaded for the most bots but this plugin allow management of it for the other bots.


# Features

* [Open Graph](http://ogp.me/) support.
* [Twitter Cards](https://dev.twitter.com/cards/overview) support. You can select between Summary and Large cards.


# Installation

The command using **gpm** is not yet available.

Manually installation shall be done. In `user/plugins` folder, apply the following command:
```
git clone https://github.com/clemdesign/grav-plugin-social-seo-metatags social-seo-metatags
```

This will clone this repository into the _social-seo-metatags_ folder.


# Usage

Plugin no need to edit any template :)

After enabling plugin, for Social integration, no action to do !

For SEO, you can define `robots` and `keywords` meta-tag content from header of markdown page. For this, add the following line:
```md
---
robots: noindex, nofollow, ...
keywords: key1, key2, key3, ...
---
Page content
```



# Configuration

## Common SEO

For SEO, you have the following default configuration:
```
seo:
  robots: without
  length: 20
  taxonomy:
    enabled: true
  page_content:
    enabled: false
```

`robots` or "Default Robots Meta-Tag" allow you to define default rules for bots. You have the following possibilities:
- noindex: prevents the page from being indexed
- nofollow: don't follow links from this page when looking for new pages to crawl
- nosnippet: don't show a snippet of this page when displaying it in the search results
- noodp: don't use text from ODP (The Open Directory Project a.k.a. dmoz.org) to generate a title or snippet for this page
- noarchive: don't display a "Cached" link for this page in the search results

`length` or "Number of keywords" allow you to define maximum number of keywords to display in meta-tag.

`taxonomy.enabled` or "Use Taxonomy Keywords" allow you to use Keywords of Taxonomy for Meta-Tags.

`page_content.enabled` or "Use Page Content Keywords" allow you to use Keywords of page content (`<strong>` and `<em>`) for Meta-Tags.

## Associate Twitter account


For Twitter, you have the following default configuration:
```
social_pages:
  pages:
    twitter:
      enabled: true
      type: summary
      username: ''
```

`enabled` or "Twitter Cards active" enable integration of [Twitter Cards](https://dev.twitter.com/cards/overview) meta-tags.

`type` or "Card Type" define the typê of card used by meta-tag. Accepted values are: `summary`, `summary_large_image`.

`username` or "Twitter Username" is your twitter account reachable by https://twitter/username.

## Associate Facebook App Id

For Facebook, you have the following default configuration:
```
social_pages:
  pages:
    facebook:
      enabled: false
      appid: '1234567890'
```

`enabled` or "Facebook active" enable integration of [Facebook Open Graph](https://developers.facebook.com/docs/opengraph/getting-started) meta-tags.

You need to generate an app_id. Without this property you will lose admin right on the Open Graph Facebook Page.

# Demo

Check source of the following link: [http://www.clemdesign.fr/blog/3-outils-pour-mettre-de-la-couleur-syntaxique-a-vos-articles](http://www.clemdesign.fr/blog/3-outils-pour-mettre-de-la-couleur-syntaxique-a-vos-articles)

# Contributing

If you think any implementation are just not the best, feel free to submit ideas and pull requests. All your comments and suggestion are welcome.

# Credits

Some features based on [Social Meta Tags Plugin](https://github.com/tucho235/grav-plugin-social-meta-tags)

