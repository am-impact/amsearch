(function($) {

Craft.AmSearch = Garnish.Base.extend(
{
    collectionHandle: null,
    isFuzzySearch: null,
    fuzzyOptions: {
        pre: "<b>",
        post: "</b>",
        extract: function(el) { return el.fuzzy; }
    },
    isLoading: false,

    searchTimer: false,
    searchResults: [],

    $searchField: $('.amsearch .amsearch--keywords'),
    $resultsContainer: $('.amsearch .amsearch--results'),
    $spinner: $('.amsearch .spinner'),

    /**
     * Initiate AmSearch.
     */
    init: function(collectionHandle, isFuzzySearch) {
        this.collectionHandle = collectionHandle;
        this.isFuzzySearch = isFuzzySearch;

        // Make sure we already fetch data
        if (isFuzzySearch) {
            this.getCollectionData();
        }

        this.addListener(this.$searchField, 'keyup', 'search');
    },

    /**
     * Search for data.
     */
    search: function() {
        var self = this,
            searchFor = self.$searchField.val();

        if (! self.isLoading) {
            if (self.isFuzzySearch) {
                var filtered = fuzzy.filter(searchFor, self.searchResults, self.fuzzyOptions);

                // Process the results to extract the strings
                var results = filtered.map(function(el) {
                    return '<p>' + el.string + '</p>';
                });

                // Set filtered results
                self.$resultsContainer.html(results.join(''));
            }
            else {
                if (self.searchTimer) {
                    clearTimeout(self.searchTimer);
                }
                if (searchFor.trim() != '') {
                    self.searchTimer = setTimeout($.proxy(function() {
                        self.getCollectionData();
                    }, this), 600);
                }
                else {
                    self.$resultsContainer.html('');
                }
            }
        }
    },

    /**
     * Get collection data.
     */
    getCollectionData: function() {
        var self = this,
            data = {
                collections: this.collectionHandle,
                params: {
                    keywords: self.$searchField.val()
                }
            };

        self.isLoading = true;
        self.$spinner.removeClass('hidden');
        self.$resultsContainer.html('');

        // Disable timer if set
        if (self.searchTimer) {
            clearTimeout(self.searchTimer);
            self.searchTimer = false;
        }

        Craft.postActionRequest('amSearch/search/getResults', data, $.proxy(function(response, textStatus) {
            if (textStatus == 'success') {
                self.isLoading = false;

                self.$spinner.addClass('hidden');

                // Did we find any results?
                if (response.success) {
                    // Give our data to the search results
                    self.searchResults = response.results;

                    if (self.isFuzzySearch) {
                        // Start the first fuzzy search!
                        self.search();
                    }
                    else {
                        // Process the results to extract the strings
                        var results = self.searchResults.map(function(el) {
                            var title = (el['title'] != undefined ? '<strong>' + el.title + '</strong><br>' : '');

                            return '<p>' + title + el.excerpt + '</p>';
                        });

                        // Set results
                        self.$resultsContainer.html(results.join(''));
                    }
                }
            }
        }, this));
    }
});

})(jQuery);
