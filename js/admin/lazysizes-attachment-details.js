var templateString = `
<span class="setting lazysizes-blurhash">
	<span class="name">Lazysizes Blurhash</span>
	<span class="value" <%= !lazysizesBlurhash ? 'style="padding-top: 0;"' : '' %>>
		<%if (!lazysizesBlurhash) {%>
			<span>
				<%= lazysizesStrings.notGenerated %>
			</span>
			<button type="button" class="button button-primary lazysizes-blurhash-generate"><%= lazysizesStrings.generate %></button>
		<%} else {%>
			<div style="padding-bottom: 8px;">
				<%= lazysizesStrings.current + lazysizesBlurhash %>
			</div>
			<button type="button" class="button lazysizes-blurhash-delete"><%= lazysizesStrings.delete %></button>
		<%}%>
	</span>
</span>
<p class="description">
	<%= lazysizesStrings.description %>
</p>
`;

var templateFunction = _.template(templateString);

// Based on code by Thomas Griffin.
// See https://gist.github.com/sunnyratilal/5650341.
wp.media.view.Attachment.Details.TwoColumn = wp.media.view.Attachment.Details.TwoColumn.extend({
    initialize: function(){
        wp.media.view.Attachment.Details.prototype.initialize.apply(this, arguments);
        // Always make sure that our content is up to date.
		this.listenTo(this.model, 'change', this.render);
    },
    render: function(){
        // Ensure that the main attachment fields are rendered.
		wp.media.view.Attachment.prototype.render.apply(this, arguments);

		this.model.fetch();

        // Detach the views, append our custom fields, make sure that our data is fully updated and re-render the updated view.
        this.views.detach();
		if(this.model.attributes.type === 'image') {
			this.$el.find( '.settings' ).append(templateFunction(this.model.toJSON()));
		}
        this.views.render();

        return this;
    }
});
wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({
    initialize: function(){
        // Always make sure that our content is up to date.
        this.listenTo(this.model, 'change', this.render);
    },
    render: function(){
        // Ensure that the main attachment fields are rendered.
		wp.media.view.Attachment.prototype.render.apply(this, arguments);

		this.model.fetch();

        // Detach the views, append our custom fields, make sure that our data is fully updated and re-render the updated view.
        this.views.detach();
		this.$el.append(templateFunction(this.model.toJSON()));
        this.views.render();

        return this;
    }
});

function lazysizesAjax(action, attachmentId, callback) {
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'lazysizes_blurhash',
			nonce: '',
			mode: action,
			attachmentId
		},
		success: callback,
		error: callback
	})
}
