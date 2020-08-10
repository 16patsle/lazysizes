const templateString = `
<%if (typeof lazysizesBlurhash !== 'undefined') {%>
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
<%}%>
`;

const templateFunction = _.template(templateString);

const clickFunction = function(e) {
	let action = '';
	if(e.target.classList.contains('lazysizes-blurhash-generate')) {
		action = 'generate';
	} else if(e.target.classList.contains('lazysizes-blurhash-delete')) {
		action = 'delete';
	} else {
		return;
	}

	this.model.set('lazysizesLoading', true);

	lazysizesAjax(action, this.model.attributes.id, this.model.attributes.nonces.lazysizes[action], (response, status, errorCode) => {
		this.model.set('lazysizesLoading', false);

		if(status === 'error') {
			this.model.set('lazysizesError', `${lazysizesStrings.error} (${errorCode})`)
		} else {
			if (response.success) {
				if (action === 'generate') {
					this.model.set('lazysizesBlurhash', response.blurhash)
				} else if (action === 'delete') {
					this.model.set('lazysizesBlurhash', false)
				}
			} else {
				this.model.set('lazysizesError', response.data[0].message)
			}
		}
	})
}

// Based on code by Thomas Griffin.
// See https://gist.github.com/sunnyratilal/5650341.

const mediaTwoColumn = wp.media.view.Attachment.Details.TwoColumn

// In Media Library.
wp.media.view.Attachment.Details.TwoColumn = mediaTwoColumn.extend({
    initialize: function(){
		mediaTwoColumn.prototype.initialize.apply(this, arguments);

        // Always make sure that our content is up to date.
		this.listenTo(this.model, 'change', this.render);
	},
	events: {
		'click .setting.lazysizes-blurhash .button': clickFunction
	},
    render: function(){
        // Ensure that the main attachment fields (and the fields of other plugins) are rendered.
		mediaTwoColumn.prototype.render.apply(this, arguments);

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

const mediaAttachmentDetails = wp.media.view.Attachment.Details

// In post editor, when selecting attachment.
wp.media.view.Attachment.Details = mediaAttachmentDetails.extend({
    initialize: function(){
		mediaAttachmentDetails.prototype.initialize.apply(this, arguments);

        // Always make sure that our content is up to date.
		this.listenTo(this.model, 'change', this.render);
	},
	events: {
		'click .setting.lazysizes-blurhash .button': clickFunction
	},
    render: function(){
        // Ensure that the main attachment fields (and the fields of other plugins) are rendered.
		mediaAttachmentDetails.prototype.render.apply(this, arguments);

		if(this.model.changedAttributes(['lazysizesBlurhash', 'lazysizesError']) === false) {
			this.model.fetch();
		}

        // Detach the views, append our custom fields, make sure that our data is fully updated and re-render the updated view.
		this.views.detach();
		if(this.model.attributes.type === 'image') {
			this.$el.append(templateFunction(this.model.toJSON()));
		}
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
