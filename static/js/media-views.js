(function($) {

  var l10n = wp.media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;

  var mediaFrameSelect = wp.media.view.MediaFrame.Select;
  wp.media.view.MediaFrame.Select = mediaFrameSelect.extend({

    bindHandlers: function() {
      this.on( 'router:create:browse', this.createRouter, this );
      this.on( 'router:render:browse', this.browseRouter, this );
      this.on( 'content:create:browse', this.browseContent, this );
      this.on( 'content:render:upload', this.uploadContent, this );
      this.on( 'toolbar:create:select', this.createSelectToolbar, this );

      this.on( 'content:create:dreamstime', this.dreamstimeContent, this );
      this.on( 'content:render:dreamstime', this.dreamstimeContent, this );
    },

    browseRouter: function( view ) {
      view.set({
        upload: {
          text:     l10n.uploadFilesTitle,
          priority: 20
        },
        browse: {
          text:     l10n.mediaLibraryTitle,
          priority: 40
        },
        dreamstime: {
          text:     'Dreamstime',
          priority: 60
        }
      });
    },

    dreamstimeContent: function(content) {
      this.$el.removeClass('hide-toolbar');
      this.state().set('src', dreamstimeIframeSrc); //set in Dreamstime::loadCssJs with wp_localize_script()
      content.view = new wp.media.view.Iframe({
        controller: this
      });
    }

  });

  var mediaFramePost = wp.media.view.MediaFrame.Post;
  wp.media.view.MediaFrame.Post = mediaFramePost.extend({

    browseRouter: function( view ) {
      view.set({
        upload: {
          text:     l10n.uploadFilesTitle,
          priority: 20
        },
        browse: {
          text:     l10n.mediaLibraryTitle,
          priority: 40
        },
        dreamstime: {
          text:     'Dreamstime',
          priority: 60
        }
      });
    },

    dreamstimeContent: function(content) {
      this.$el.removeClass('hide-toolbar');
      this.state().set('src', dreamstimeIframeSrc); //set in Dreamstime::loadCssJs with wp_localize_script()
      content.view = new wp.media.view.Iframe({
        controller: this
      });
    }

  });

}(jQuery));