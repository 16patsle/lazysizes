var templateString = "<span class='setting custom-thing'>" +
	                        "<span class='name'>Blurhash</span>" +
	                        "<span class='value'><%= lazysizesBlurhash %></span>" +
	                        '</span>';

// Based on code by Thomas Griffin.
// See https://gist.github.com/sunnyratilal/5650341.
var extendObject = {
    initialize: function(){
        // Always make sure that our content is up to date.
        this.listenTo(this.model, 'change', this.render);
    },
    render: function(){
        // Ensure that the main attachment fields are rendered.
        wp.media.view.Attachment.prototype.render.apply(this, arguments);

        // Detach the views, append our custom fields, make sure that our data is fully updated and re-render the updated view.
        this.views.detach();
		this.$el.find( '.settings' ).append(_.template(templateString)(this.model.toJSON()));
		console.log(this.model.toJSON());
        this.model.fetch();
        this.views.render();

        return this;
    }
}

wp.media.view.Attachment.Details.TwoColumn = wp.media.view.Attachment.Details.TwoColumn.extend(extendObject);
wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend(extendObject);
