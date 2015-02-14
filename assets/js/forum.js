function formatForumQuote(author, quote)
{
    quote = "**" + author + "** said:\n\n" + quote;
    quote = quote.replace(/^/g, ">");
    quote = quote.replace(/\n/g, "\n>");

    return quote;
}

$(document).ready(function() {
	$(".quote").click(function (e) {
		var postId = $(this).closest('.forum-post').data('post-id');
		$.request('onQuote', {
			data: { id: postId },
		    success: function(data)
		    {
			    var obj = $.parseJSON(data.result);
		        var quoteBody = obj.content;
		        var authorName = obj.author;
		       	var quoteText = formatForumQuote(authorName, quoteBody);

	          	 $('#topicContent').val($('#topicContent').val() + quoteText + '\n\n').focus();
		    }
		});
	});
});