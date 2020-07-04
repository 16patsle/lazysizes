var templateString = "<span class='setting custom-thing'>" +
	                        "<span class='name'>Blurhash</span>" +
	                        "<span class='value'><%= lazysizesBlurhash || lazysizesStrings.notGenerated %></span>" +
                            '</span>';

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
