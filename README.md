amsearch
========

```
{% set keywords = craft.request.getParam('keywords') ?: false %}
{% set criteria = {
    collections: ['normaal'],
    params: {
        limit: 2,
        keywords: keywords
    }
} %}

{% amSearchPaginate criteria as searchResults %}
    {# Search results #}
    {% if not searchResults|length %}
        <p>{{ 'Er zijn geen zoekresultaten.' }}</p>
    {% else %}
        {% for searchResult in searchResults %}
            <div>
                <p>
                    <strong>{{ searchResult.title }}</strong><br>
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
