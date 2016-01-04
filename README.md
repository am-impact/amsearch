# amsearch

_Searching in Craft_

## Searching

Normally you can search in one Element Type at a time, when sometimes, you want to search in multiple. E.g. you want to display entries and users based on the keywords that are given. You also want to display an excerpt, which is built based on the search keywords.

### Collections

This is where you decide what Element Type should be searched for.

If an Element Type doesn't have an URL by itself (E.g. users), you can set a **custom URL format** in the collection's settings. E.g.: **_users/{id}/{firstName}-{lastName}_**

![NewCollection](https://raw.githubusercontent.com/am-impact/am-impact.github.io/master/img/readme/amsearch/new-collection.png "NewCollection")

### Search types

There are two types available.

The **standard** type, is the one that'll be used the most. It uses Craft's search functionality, but without the use of records & models. This means that anything that is related (relations) to this Element Type, is **not** available in the search result.

It is probably a bit confusing when you see the **fuzzy** type. While building this plugin, the original idea was that one should be able to get all available search results at once, and then **filter** based on the user's input. Basically this'll look like functionality that has been used in [a&m command](https://github.com/am-impact/amcommand). In order to use this, you can use the **fuzzy.min.js** in the resources folder of this plugin.

### Settings

![Settings](https://raw.githubusercontent.com/am-impact/am-impact.github.io/master/img/readme/amsearch/settings.png "Settings")

### Example: Search results template

Based on a **standard** collection type. You can search in multiple collections when you give an array to the collections parameter. E.g.: **collections: ['entries', 'users', 'assets']**.

```
{% set keywords = craft.request.getParam('keywords') ?: false %}
{% set criteria = {
    collections: ['entries'],
    params: {
        limit: 2,
        keywords: keywords
    }
} %}

{% amSearchPaginate criteria as searchResults %}
    {# Search results #}
    {% if not searchResults|length %}
        <p>{{ 'There are no search results.' }}</p>
    {% else %}
        {% for searchResult in searchResults %}
            <div>
                <p>
                    {% if searchResult['title'] is defined %}<strong>{{ searchResult.title }}</strong><br>{% endif %}
                    {{ searchResult.excerpt }}
                </p>
            </div>
        {% endfor %}

        {# Pagination #}
        <a href="{{ amSearchPaginate.firstUrl }}">First Page</a>
        {% if amSearchPaginate.prevUrl %}<a href="{{ amSearchPaginate.prevUrl }}">Previous Page</a>{% endif %}

        {% for page, url in amSearchPaginate.getPrevUrls(5) %}
            <a href="{{ url }}">{{ page }}</a>
        {% endfor %}

        <span class="current">{{ amSearchPaginate.currentPage }}</span>

        {% for page, url in amSearchPaginate.getNextUrls(5) %}
            <a href="{{ url }}">{{ page }}</a>
        {% endfor %}

        {% if amSearchPaginate.nextUrl %}<a href="{{ amSearchPaginate.nextUrl }}">Next Page</a>{% endif %}
        <a href="{{ amSearchPaginate.lastUrl }}">Last Page</a>
    {% endif %}
```
