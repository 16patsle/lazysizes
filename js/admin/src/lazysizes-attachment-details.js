var templateString = `
<span class="setting lazysizes-blurhash">
	<span class="name">Lazysizes Blurhash</span>
	<span class="value" <%= !lazysizesBlurhash ? 'style="padding-top: 0;"' : '' %>>
		<%if (!lazysizesBlurhash) {%>
			<span>
				<%= lazysizesStrings.notGenerated %>
			</span>
			<button type="button" class="button button-primary lazysizes-blurhash-generate" <%= lazysizesLoading ? 'disabled' : '' %>><%= lazysizesStrings.generate %></button>
		<%} else {%>
			<div style="padding-bottom: 8px;">
				<%= lazysizesStrings.current + lazysizesBlurhash %>
			</div>
			<button type="button" class="button lazysizes-blurhash-delete" <%= lazysizesLoading ? 'disabled' : '' %>><%= lazysizesStrings.delete %></button>
		<%}%>
		<span class="spinner <%= lazysizesLoading ? 'is-active' : '' %>" style="padding-top: 0; float: none; min-width: 20px;"></span>
		<%if (lazysizesError) {%>
			<div>
				<%= lazysizesError %>
			</div>
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
	events: {
		'click .setting.lazysizes-blurhash .button': function(e) {
			var action = '';
			if(e.target.classList.contains('lazysizes-blurhash-generate')) {
				action = 'generate';
			} else if(e.target.classList.contains('lazysizes-blurhash-delete')) {
				action = 'delete';
			} else {
				return;
			}

			this.model.set('lazysizesLoading', true);

			var model = this.model
			lazysizesAjax(action, model.attributes.id, model.attributes.nonces.lazysizes[action], function(response, status, errorCode) {
				model.set('lazysizesLoading', false);

				if(status === 'error') {
					model.set('lazysizesError', lazysizesStrings.error + ' (' + errorCode + ')')
				} else {
					if (response.success) {
						if (action === 'generate') {
							model.set('lazysizesBlurhash', response.blurhash)
						} else if (action === 'delete') {
							model.set('lazysizesBlurhash', false)
						}
					} else {
						model.set('lazysizesError', response.data[0].message)
					}
				}
			})
		}
	},
    render: function(){
        // Ensure that the main attachment fields are rendered.
		wp.media.view.Attachment.prototype.render.apply(this, arguments);

		if(this.model.changedAttributes(['lazysizesBlurhash', 'lazysizesError']) === false) {
			this.model.fetch();
		}

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

function lazysizesAjax(action, attachmentId, nonce, callback) {
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'lazysizes_blurhash',
			nonce,
			mode: action,
			attachmentId
		},
		success: callback,
		error: callback
	})
}
