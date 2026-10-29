/*
 * Forum, quotes a post into the reply form when its Quote link is clicked.
 * Uses the AJAX framework without jQuery.
 */
(function() {
    'use strict';

    // Each line of the post becomes a Markdown quote, led by its author
    function formatForumQuote(author, quote) {
        quote = '**' + author + '** said:\n\n' + quote;
        quote = quote.replace(/^/, '>');
        quote = quote.replace(/\n/g, '\n>');

        return quote;
    }

    function onClickQuoteButton(ev) {
        var button = ev.target.closest('[data-quote-button]');
        if (!button) {
            return;
        }

        ev.preventDefault();

        oc.request(button, 'onQuote', {
            success: function(data) {
                var textarea = document.getElementById('topicContent');
                if (!textarea) {
                    return;
                }

                textarea.value += formatForumQuote(data.author, data.content) + '\n\n';
                textarea.focus();
            }
        });
    }

    document.addEventListener('click', onClickQuoteButton);
})();
