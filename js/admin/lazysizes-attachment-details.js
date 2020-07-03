// Based on code by Thomas Griffin.
// See https://gist.github.com/sunnyratilal/5650341.
wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({
    initialize: function(){
        // Always make sure that our content is up to date.
        this.model.on('change', this.render, this);
    },
    render: function(){
        // Ensure that the main attachment fields are rendered.
        wp.media.view.Attachment.prototype.render.apply(this, arguments);

        // Detach the views, append our custom fields, make sure that our data is fully updated and re-render the updated view.
        this.views.detach();
        this.$el.append(wp.media.template('attatchment-fields-TODO-REPLACE')(this.model.toJSON()));
        this.model.fetch();
        this.views.render();

        // This is the preferred convention for all render functions.
        return this;
    }
});
console.log('HELLO!')
